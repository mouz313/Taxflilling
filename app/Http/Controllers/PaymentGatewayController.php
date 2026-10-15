<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;

class PaymentGatewayController extends Controller
{
    protected PaymentGatewayService $paymentService;

    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get Payment Details & Pay via Selected Pakistani Gateway (Read-only token/details)
     */
    public function initiatePayment(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string'],
            'gateway'         => ['required', 'in:jazzcash,bank_transfer'],   // jo gateways verified hain sirf wohi
            'mobile_number'   => ['nullable', 'string'],
        ]);

        // ID guess karne se tracking number nahi milna chahiye
        abort_unless(hash_equals($serviceRequest->tracking_number, strtoupper($validated['tracking_number'])), 404);
        abort_if($serviceRequest->payment_status === 'paid', 409, 'Order pehle se paid hai.');

        $gateway = $validated['gateway'];

        try {
            $details = match ($gateway) {
                'jazzcash'      => $this->paymentService->createJazzCashCheckout($serviceRequest, $validated['mobile_number'] ?? ''),
                'bank_transfer' => $this->paymentService->getBankTransferDetails($serviceRequest),
            };
        } catch (\RuntimeException $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Online payment abhi available nahi. Bank transfer ya hamari team se rabta karein.',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'details' => $details,
        ]);
    }

    /**
     * Webhook / IPN Handler for Gateways (POST only, HMAC verified, idempotent)
     */
    public function handleCallback(Request $request, string $gateway)
    {
        if (! $request->isMethod('post')) {
            abort(405, 'Only POST webhooks are accepted.');
        }

        $billReference = (string) $request->input('pp_BillReference');
        $ip = $request->ip();

        // EasyPaisa / 1Bill: apna verified handler banne tak band
        if ($gateway !== 'jazzcash') {
            \App\Models\PaymentEvent::logEvent($gateway, null, 'rejected', 'Gateway callback not enabled', null, $ip, $request->all());
            return response()->json(['error' => 'Gateway callback not enabled.'], 501);
        }

        // 1. Pehle signature (order lookup se pehle, taake info leak na ho)
        if (! $this->paymentService->verifyJazzCashHash($request->all())) {
            \App\Models\PaymentEvent::logEvent('jazzcash', $billReference ?: null, 'rejected', 'Invalid payment signature hash', null, $ip, $request->except(['pp_SecureHash']));
            return response()->json(['error' => 'Invalid payment signature hash.'], 403);
        }

        $order = ServiceRequest::where('tracking_number', $billReference)->first();
        if (! $order) {
            \App\Models\PaymentEvent::logEvent('jazzcash', $billReference, 'rejected', 'Order not found', null, $ip);
            return response()->json(['error' => 'Order not found.'], 404);
        }

        // 2. Gateway ne success kaha?
        if ($request->input('pp_ResponseCode') !== '000') {
            \App\Models\PaymentEvent::logEvent('jazzcash', $billReference, 'rejected', 'Transaction declined: ' . $request->input('pp_ResponseCode'), $order->id, $ip);
            return response()->json(['error' => 'Transaction declined by gateway.'], 400);
        }

        // 3. Amount exact match (pp_Amount lazmi, paisa mein)
        $received = (int) $request->input('pp_Amount');
        $expected = (int) round($order->fee_amount * 100);
        if ($received !== $expected) {
            \App\Models\PaymentEvent::logEvent('jazzcash', $billReference, 'rejected', 'Amount mismatch: received ' . $received . ' expected ' . $expected, $order->id, $ip);
            return response()->json(['error' => 'Paid amount does not match order fee.'], 422);
        }

        $txnRef = (string) $request->input('pp_TxnRefNo');
        if ($txnRef === '') {
            \App\Models\PaymentEvent::logEvent('jazzcash', $billReference, 'rejected', 'Missing transaction reference', $order->id, $ip);
            return response()->json(['error' => 'Missing transaction reference.'], 400);
        }

        // 4. Idempotent, concurrent callbacks mein bhi ek hi baar update
        $result = DB::transaction(function () use ($order, $txnRef) {
            $locked = ServiceRequest::whereKey($order->id)->lockForUpdate()->first();

            if ($locked->payment_status === 'paid') {
                return 'already_processed';
            }

            $locked->update([
                'payment_status'    => 'paid',
                'payment_method'    => 'jazzcash',
                'payment_reference' => $txnRef,
                'status'            => $locked->status === 'submitted' ? 'in_review' : $locked->status,
            ]);

            return 'success';
        });

        \App\Models\PaymentEvent::logEvent(
            'jazzcash',
            $billReference,
            $result,
            $result === 'already_processed' ? 'Duplicate callback processed idempotently' : 'Payment verified successfully',
            $order->id,
            $ip
        );

        return $request->wantsJson()
            ? response()->json(['status' => $result, 'reference' => $txnRef])
            : redirect()->route('dashboard')->with('success', 'Payment verify ho gayi.');
    }
}
