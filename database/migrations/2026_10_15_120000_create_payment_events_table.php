<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->string('gateway', 50);
            $table->foreignId('service_request_id')->nullable()->constrained('service_requests')->nullOnDelete();
            $table->string('tracking_number', 50)->nullable()->index();
            $table->string('result', 50)->index(); // accepted, already_processed, rejected
            $table->string('reason')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('payload_hash', 64)->nullable(); // sha256 of payload without sensitive PII
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
