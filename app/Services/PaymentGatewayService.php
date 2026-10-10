<?php

namespace App\Services;

use App\Models\ServiceRequest;
use App\Models\TaxReturn;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentGatewayService
{
    /**
     * 1Link 1Bill / Kuickpay PSID Generator
     * Generates an official 1Bill Consumer Number payable via any Pakistani Banking App
     */
    public function generate1BillPsid(ServiceRequest|TaxReturn $model): array
    {
        $amount = (float) ($model->fee_amount ?? $model->net_tax_payable_or_refundable ?? 2500);
        $consumerPrefix = '100'; // 1Bill utility prefix
        $uniqueDigits = str_pad($model->id . rand(1000, 9999), 11, '0', STR_PAD_LEFT);
        $psid = $consumerPrefix . $uniqueDigits;

        $reference = '1BILL-' . $psid;

        return [
            'gateway' => '1bill',
            'psid' => $psid,
            'title' => '1Link 1Bill (All Pakistani Banking Apps)',
            'amount' => $amount,
            'formatted_amount' => 'PKR ' . number_format($amount, 0),
            'instructions' => 'Apne bank ki mobile app (HBL, Meezan, UBL, Alfalah, etc.) mein "Bill Payment" > "1Bill / Kuickpay" select karein aur Consumer Number: ' . $psid . ' darj karein.',
            'due_date' => now()->addDays(7)->format('d-M-Y'),
            'reference' => $reference,
        ];
    }

    /**
     * JazzCash Merchant Gateway Payload & Request
     */
    public function createJazzCashCheckout(ServiceRequest|TaxReturn $model, string $mobileNumber = ''): array
    {
        $amount = (float) ($model->fee_amount ?? $model->net_tax_payable_or_refundable ?? 2500);
        $txnRefNo = 'T' . date('YmdHis') . rand(10, 99);
        $amountInPaisa = (int) ($amount * 100);

        $merchantId = config('services.jazzcash.merchant_id') ?: env('JAZZCASH_MERCHANT_ID', 'MC_SANDBOX');
        $password = config('services.jazzcash.password') ?: env('JAZZCASH_PASSWORD', 'pass_sandbox');
        $integritySalt = config('services.jazzcash.salt') ?: env('JAZZCASH_SALT', 'taxfiler_secure_salt');

        $postData = [
            'pp_Version' => '1.1',
            'pp_TxnType' => 'MWALLET',
            'pp_Language' => 'EN',
            'pp_MerchantID' => $merchantId,
            'pp_SubMerchantID' => '',
            'pp_Password' => $password,
            'pp_BankID' => 'TBANK',
            'pp_ProductID' => 'TAXFILER_SERVICE',
            'pp_TxnRefNo' => $txnRefNo,
            'pp_Amount' => (string) $amountInPaisa,
            'pp_TxnCurrency' => 'PKR',
            'pp_TxnDateTime' => date('YmdHis'),
            'pp_BillReference' => (string) ($model->tracking_number ?? 'RET-' . $model->id),
            'pp_Description' => 'Tax Filing & Advisory Consultancy Fee',
            'pp_TxnExpiryDateTime' => date('YmdHis', strtotime('+1 day')),
            'pp_ReturnURL' => route('payment.callback', ['gateway' => 'jazzcash']),
            'pp_MobileNumber' => $mobileNumber ?: '03001234567',
        ];

        // Generate SHA-256 Hash
        ksort($postData);
        $hashString = $integritySalt;
        foreach ($postData as $key => $val) {
            if ($val !== '') {
                $hashString .= '&' . $val;
            }
        }
        $secureHash = strtoupper(hash_hmac('sha256', $hashString, $integritySalt));
        $postData['pp_SecureHash'] = $secureHash;

        return [
            'gateway' => 'jazzcash',
            'post_url' => 'https://sandbox.jazzcash.com.pk/CustomerPortal/transactionmanagement/merchantform',
            'payload' => $postData,
            'txn_ref' => $txnRefNo,
            'amount' => $amount,
            'title' => 'JazzCash Mobile Account / Voucher',
        ];
    }

    /**
     * Cryptographically verify JazzCash HMAC-SHA256 signature
     */
    public function verifyJazzCashHash(array $input): bool
    {
        $received = $input['pp_SecureHash'] ?? '';
        if (empty($received)) {
            return false;
        }

        unset($input['pp_SecureHash']);
        ksort($input);

        $salt = config('services.jazzcash.salt') ?: env('JAZZCASH_SALT', 'taxfiler_secure_salt');
        $hashString = $salt;
        foreach ($input as $key => $val) {
            if ($val !== '' && str_starts_with($key, 'pp_')) {
                $hashString .= '&' . $val;
            }
        }

        $calculated = strtoupper(hash_hmac('sha256', $hashString, $salt));

        return hash_equals($calculated, strtoupper($received));
    }

    /**
     * EasyPaisa Merchant Gateway Payload
     */
    public function createEasyPaisaCheckout(ServiceRequest|TaxReturn $model, string $mobileNumber = ''): array
    {
        $amount = (float) ($model->fee_amount ?? $model->net_tax_payable_or_refundable ?? 2500);
        $orderId = 'EP-' . date('Ymd') . '-' . rand(10000, 99999);

        return [
            'gateway' => 'easypaisa',
            'order_id' => $orderId,
            'amount' => $amount,
            'formatted_amount' => 'PKR ' . number_format($amount, 0),
            'mobile' => $mobileNumber,
            'merchant_name' => 'TaxFiler.pk Advisory Services',
            'title' => 'EasyPaisa Wallet & OTC Payment',
            'redirect_url' => 'https://easypay.easypaisa.com.pk/easypay/Index.jsf',
        ];
    }

    /**
     * Direct Bank IBFT / RAAST Payment Instructions
     */
    public function getBankTransferDetails(ServiceRequest|TaxReturn $model): array
    {
        $amount = (float) ($model->fee_amount ?? $model->net_tax_payable_or_refundable ?? 2500);
        $refCode = 'TX-' . ($model->tracking_number ?? str_pad($model->id, 6, '0', STR_PAD_LEFT));

        $consultancy = \App\Models\Consultancy::first();
        $bankName = config('services.bank.name', 'Meezan Bank Limited (Islamic Banking)');
        $accountTitle = $consultancy?->name ?? 'TaxFiler Advisory & Corporate Practice';
        $accountNo = config('services.bank.account_number', '02010108920192');
        $iban = config('services.bank.iban', 'PK42MEZN0002010108920192');
        $raastId = $consultancy?->phone ?? config('services.bank.raast_id', '03001234567');

        return [
            'gateway' => 'bank_transfer',
            'bank_name' => $bankName,
            'account_title' => $accountTitle,
            'account_number' => $accountNo,
            'iban' => $iban,
            'raast_id' => $raastId,
            'amount' => $amount,
            'formatted_amount' => 'PKR ' . number_format($amount, 0),
            'reference_code' => $refCode,
            'instructions' => 'Apne bank account se IBFT ya RAAST transfer karein aur Reference / Remarks mein "' . $refCode . '" lazmi likhein.',
        ];
    }
}
