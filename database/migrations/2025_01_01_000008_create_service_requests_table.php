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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultancy_id')->nullable()->constrained('consultancies')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            
            // 1 of 12 Services
            $table->string('service_code', 50); 
            $table->string('service_name');
            $table->string('filer_type', 30)->default('not_applicable'); // fresh_filer, already_filer, not_applicable
            
            // Financial & Payment
            $table->decimal('fee_amount', 10, 2)->default(0);
            $table->string('payment_status', 20)->default('pending'); // pending, paid
            $table->string('payment_method', 30)->nullable(); // jazzcash, bank_transfer, card
            $table->string('payment_reference')->nullable();
            
            // Lifecycle Status
            $table->string('status', 30)->default('draft'); // draft, submitted, in_review, completed, cancelled
            
            // User submitted inputs (JSON payload for dynamic service questions)
            $table->json('data_payload')->nullable();
            
            // Consultant Delivery
            $table->string('delivered_file_path')->nullable(); // Final FBR CPR / SECP Certificate / NTN Slip PDF
            $table->string('delivered_reference_no')->nullable(); // e.g. 114(1) No or NTN or Incorporation #
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
