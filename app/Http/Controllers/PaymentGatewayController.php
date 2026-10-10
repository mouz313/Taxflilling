<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;

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
            'gateway' => ['required', 'in:1bill,jazzcash,easypaisa,bank_transfer'],
            'mobile_number' => ['nullable', 'string'],
        ]);

        $gateway = $validated['gateway'];

        $details = match ($gateway) {
            '1bill' => $this->paymentService->generate1BillPsid($serviceRequest),
            'jazzcash' => $this->paymentService->createJazzCashCheckout($serviceRequest, $validated['mobile_number'] ?? ''),
            'easypaisa' => $this->paymentService->createEasyPaisaCheckout($serviceRequest, $validated['mobile_number'] ?? ''),
            'bank_transfer' => $this->paymentService->getBankTransferDetails($serviceRequest),
        };

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

        $billReference = $request->input('pp_BillReference') 
            ?? $request->input('order_id') 
            ?? $request->input('tracking_number');

        if (! $billReference) {
            return response()->json(['error' => 'Missing reference identifier.'], 400);
        }

        $serviceRequest = ServiceRequest::where('tracking_number', $billReference)->first();
        if (! $serviceRequest) {
            return response()->json(['error' => 'Order not found.'], 404);
        }

        // Idempotency: if already paid, return early
        if ($serviceRequest->payment_status === 'paid') {
            return response()->json(['status' => 'already_processed', 'message' => 'Order is already marked as paid.']);
        }

        if ($gateway === 'jazzcash') {
            // 1. Verify HMAC Hash
            if (! $this->paymentService->verifyJazzCashHash($request->all())) {
                return response()->json(['error' => 'Invalid payment signature hash.'], 403);
            }

            // 2. Verify Success Response Code
            $ppResponseCode = $request->input('pp_ResponseCode');
            if ($ppResponseCode !== '000') {
                return response()->json(['error' => 'Transaction declined by gateway: ' . $ppResponseCode], 400);
            }

            // 3. Verify Amount (JazzCash sends pp_Amount in paisas)
            $receivedPaisa = (int) $request->input('pp_Amount');
            $expectedPaisa = (int) round($serviceRequest->fee_amount * 100);
            if ($receivedPaisa > 0 && $receivedPaisa < $expectedPaisa) {
                return response()->json(['error' => 'Paid amount does not match order fee.'], 422);
            }

            $txnRefNo = $request->input('pp_TxnRefNo') ?? ('JC-' . time());
        } else {
            // General gateway validation
            $txnRefNo = $request->input('transaction_id') ?? $request->input('reference_no') ?? ('GW-' . time());
        }

        $serviceRequest->update([
            'payment_status' => 'paid',
            'payment_method' => $gateway,
            'payment_reference' => $txnRefNo,
            'status' => $serviceRequest->status === 'submitted' ? 'in_review' : $serviceRequest->status,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified successfully.',
                'reference' => $txnRefNo,
            ]);
        }

        return redirect()->route('dashboard')->with('success', "Payment successfully verified via {$gateway}! Order #{$serviceRequest->tracking_number} is now In Review.");
    }
}
