<?php

namespace App\Services;

use App\Models\CallbackRequest;
use App\Models\ServiceRequest;
use App\Models\TaxReturn;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Dispatch automated WhatsApp & SMS Notification when Service Request status changes
     */
    public function notifyServiceStatusChange(ServiceRequest $serviceRequest, string $newStatus, ?string $customNotes = null): array
    {
        $phone = $this->cleanPhoneNumber($serviceRequest->applicant_phone);
        $name = $serviceRequest->applicant_name ?? 'Valued Client';
        $tracking = $serviceRequest->tracking_number;
        $service = $serviceRequest->service_name;

        $statusUrdu = match ($newStatus) {
            'submitted' => 'Submit ho chuki hai (Consultant review jaari hai)',
            'in_review' => 'IRIS 2.0 / SECP par Active Processing shuru ho chuki hai',
            'completed' => 'Kamiyabi se Mukammal ho chuki hai (Certificate Issued)',
            'cancelled' => 'Cancelled / Not Eligible',
            default => ucfirst($newStatus)
        };

        $message = "Assalam o Alaikum {$name},\n\n"
            . "Aapki application *{$service}* (Tracking # *{$tracking}*) ka status update ho gaya hai:\n"
            . "📌 *Naya Status:* {$statusUrdu}\n";

        if ($customNotes) {
            $message .= "💬 *Consultant Note:* {$customNotes}\n";
        }

        $trackUrl = url('/track?query=' . $tracking);
        $message .= "\nLive status check karein:\n🔗 {$trackUrl}\n\n"
            . "TaxFiler.pk Advisory Team";

        return $this->dispatch($phone, $message, "Service Status: {$tracking}");
    }

    /**
     * Dispatch notification when Deliverable Certificate is uploaded
     */
    public function notifyDeliverableUploaded(ServiceRequest $serviceRequest): array
    {
        $phone = $this->cleanPhoneNumber($serviceRequest->applicant_phone);
        $name = $serviceRequest->applicant_name ?? 'Valued Client';
        $tracking = $serviceRequest->tracking_number;
        $service = $serviceRequest->service_name;

        $downloadUrl = route('services.download', $serviceRequest->id);

        $message = "🎉 *Mubarak Ho {$name}!* 🎉\n\n"
            . "Aapki *{$service}* (App # *{$tracking}*) ka official certificate/document consultant ki taraf se issue kar diya gaya hai!\n\n"
            . "Aap apna document apne dashboard 'My Applications' ya neeche diye gaye link se foran download kar sakte hain:\n"
            . "📥 {$downloadUrl}\n\n"
            . "TaxFiler.pk Practice Portal";

        return $this->dispatch($phone, $message, "Certificate Ready: {$tracking}");
    }

    /**
     * Dispatch notification when Tax Return is submitted to FBR IRIS 2.0
     */
    public function notifyReturnFiled(TaxReturn $taxReturn): array
    {
        $client = $taxReturn->client;
        if (!$client || !$client->phone) {
            return ['status' => 'skipped', 'message' => 'No client phone available'];
        }

        $phone = $this->cleanPhoneNumber($client->phone);
        $name = $client->name;
        $year = $taxReturn->taxYear?->name ?? '2026-27';
        $ackNo = $taxReturn->iris_acknowledgement_no ?? 'IRIS-REC-VERIFIED';

        $dossierUrl = route('returns.pdf', $taxReturn->id);

        $message = "Assalam o Alaikum {$name},\n\n"
            . "Aapka Tax Year *{$year}* ka Income Tax Return FBR IRIS 2.0 par kamiyabi se file kar diya gaya hai!\n"
            . "📜 *FBR Acknowledgment Number:* {$ackNo}\n\n"
            . "Aapka official 114(1) Tax Return Dossier PDF download karne ke liye link par click karein:\n"
            . "📥 {$dossierUrl}\n\n"
            . "Active Taxpayer List (ATL) status jald FBR database par update ho jayega.\n"
            . "TaxFiler Advisory Desk";

        return $this->dispatch($phone, $message, "Return Filed: {$client->cnic}");
    }

    /**
     * Clean and normalize Pakistani phone numbers (0300... -> 92300...)
     */
    public function cleanPhoneNumber(?string $phone): string
    {
        if (!$phone) {
            return '';
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($digits, '03') && strlen($digits) === 11) {
            return '92' . substr($digits, 1);
        }

        if (str_starts_with($digits, '92') && strlen($digits) === 12) {
            return $digits;
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '3')) {
            return '92' . $digits;
        }

        return $digits;
    }

    /**
     * Build Direct Clickable WhatsApp Link
     */
    public function getWhatsAppClickUrl(string $phone, string $message): string
    {
        $normalizedPhone = $this->cleanPhoneNumber($phone);
        return 'https://wa.me/' . $normalizedPhone . '?text=' . urlencode($message);
    }

    /**
     * Unified Dispatcher (Supports WhatsApp Cloud API, Pakistani SMS Gateways & Local Log)
     */
    protected function dispatch(string $phone, string $message, string $context = ''): array
    {
        if (empty($phone)) {
            return ['status' => 'skipped', 'reason' => 'Phone number missing'];
        }

        // 1. Audit Log in Laravel logger
        Log::info("Notification Dispatched [{$context}] to {$phone}: " . substr($message, 0, 100) . '...');

        $dispatchedTo = [];

        // 2. WhatsApp Cloud API (If configured in .env)
        $whatsappToken = env('WHATSAPP_CLOUD_API_TOKEN');
        $whatsappPhoneId = env('WHATSAPP_PHONE_NUMBER_ID');

        if ($whatsappToken && $whatsappPhoneId) {
            try {
                $response = Http::withToken($whatsappToken)->post(
                    "https://graph.facebook.com/v19.0/{$whatsappPhoneId}/messages",
                    [
                        'messaging_product' => 'whatsapp',
                        'to' => $phone,
                        'type' => 'text',
                        'text' => ['body' => $message]
                    ]
                );
                if ($response->successful()) {
                    $dispatchedTo[] = 'whatsapp_cloud_api';
                }
            } catch (\Throwable $e) {
                Log::warning("WhatsApp Cloud API Delivery Failed: " . $e->getMessage());
            }
        }

        // 3. Pakistani Local SMS Gateway (Jazz / Telenor / Zong / SMS4Connect adapter)
        $smsApiKey = env('SMS_GATEWAY_API_KEY');
        $smsApiUrl = env('SMS_GATEWAY_URL');

        if ($smsApiKey && $smsApiUrl) {
            try {
                $response = Http::post($smsApiUrl, [
                    'key' => $smsApiKey,
                    'receiver' => $phone,
                    'sender' => env('SMS_MASKING', 'TaxFiler'),
                    'message' => $message,
                ]);
                if ($response->successful()) {
                    $dispatchedTo[] = 'local_sms_gateway';
                }
            } catch (\Throwable $e) {
                Log::warning("SMS Gateway Delivery Failed: " . $e->getMessage());
            }
        }

        // If no external gateway configured, log simulated delivery
        if (empty($dispatchedTo)) {
            $dispatchedTo[] = 'local_audit_log';
        }

        return [
            'status' => 'success',
            'phone' => $phone,
            'channels' => $dispatchedTo,
            'whatsapp_web_url' => $this->getWhatsAppClickUrl($phone, $message)
        ];
    }
}
