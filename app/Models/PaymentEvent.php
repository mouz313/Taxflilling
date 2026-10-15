<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'gateway',
        'service_request_id',
        'tracking_number',
        'result',
        'reason',
        'ip',
        'payload_hash',
    ];

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    /**
     * Helper to log an event safely without storing raw card/PII.
     */
    public static function logEvent(
        string $gateway,
        ?string $trackingNumber,
        string $result,
        ?string $reason = null,
        ?int $serviceRequestId = null,
        ?string $ip = null,
        array $payload = []
    ): self {
        // Compute SHA-256 hash of payload without saving raw payload
        $payloadHash = !empty($payload) ? hash('sha256', json_encode($payload)) : null;

        return self::create([
            'gateway' => $gateway,
            'tracking_number' => $trackingNumber,
            'result' => $result,
            'reason' => $reason,
            'service_request_id' => $serviceRequestId,
            'ip' => $ip,
            'payload_hash' => $payloadHash,
        ]);
    }
}
