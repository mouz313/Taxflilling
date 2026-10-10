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
     * Get Payment Details & Pay via Selected Pakistani Gateway
     */
    public function initiatePayment(Request $request, ServiceRequest $serviceRequest)
    {
        $validated = $request->validate([
            'gateway' => ['required', 'in:1bill,jazzcash,easypaisa,bank_transfer'],
            'mobile_number' => ['nullable', 'string'],
            'reference_no' => ['nullable', 'string'],
        ]);

        $gateway = $validated['gateway'];

        $details = match ($gateway) {
            '1bill' => $this->paymentService->generate1BillPsid($serviceRequest),
            'jazzcash' => $this->paymentService->createJazzCashCheckout($serviceRequest, $validated['mobile_number'] ?? ''),
            'easypaisa' => $this->paymentService->createEasyPaisaCheckout($serviceRequest, $validated['mobile_number'] ?? ''),
            'bank_transfer' => $this->paymentService->getBankTransferDetails($serviceRequest),
        };

        // When paying via confirmed transaction or consultant marking
        if ($request->has('confirm_payment')) {
            $ref = $validated['reference_no'] ?? ($details['psid'] ?? $details['order_id'] ?? ('REF-' . strtoupper(\Illuminate\Support\Str::random(8))));
            
            $serviceRequest->update([
                'payment_status' => 'paid',
                'payment_method' => $gateway,
                'payment_reference' => $ref,
                'status' => $serviceRequest->status === 'submitted' ? 'in_review' : $serviceRequest->status,
            ]);

            return back()->with('success', "Payment PKR " . number_format($serviceRequest->fee_amount, 0) . " ({$gateway}) verify ho chuki hai! Transaction Ref: {$ref}");
        }

        return response()->json([
            'success' => true,
            'details' => $details,
        ]);
    }

    /**
     * Webhook / Return URL Callback Handler for Gateways
     */
    public function handleCallback(Request $request, string $gateway)
    {
        $ppResponseCode = $request->input('pp_ResponseCode');
        $ppBillReference = $request->input('pp_BillReference');
        $txnRefNo = $request->input('pp_TxnRefNo');

        if ($ppBillReference) {
            $serviceRequest = ServiceRequest::where('tracking_number', $ppBillReference)->first();
            if ($serviceRequest && ($ppResponseCode === '000' || $request->isMethod('get'))) {
                $serviceRequest->update([
                    'payment_status' => 'paid',
                    'payment_method' => $gateway,
                    'payment_reference' => $txnRefNo ?? ('ONLINE-' . time()),
                    'status' => $serviceRequest->status === 'submitted' ? 'in_review' : $serviceRequest->status,
                ]);

                return redirect()->route('dashboard')->with('success', "Payment successfully received via {$gateway}! Order status is now In Review.");
            }
        }

        return redirect()->route('dashboard')->with('info', "Payment callback received from {$gateway}.");
    }
}
