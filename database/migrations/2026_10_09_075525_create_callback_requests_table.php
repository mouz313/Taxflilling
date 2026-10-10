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
        Schema::create('callback_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultancy_id')->nullable()->constrained('consultancies')->nullOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->string('service_code')->nullable();
            $table->string('service_name')->nullable();
            $table->string('preferred_time')->default('anytime'); // morning, afternoon, evening, anytime
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, contacted, converted, closed
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('admin_response_notes')->nullable();
            $table->timestamps();
        });

        // Make user_id nullable on service_requests to support guest applications seamlessly
        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('tracking_number', 30)->nullable()->unique()->after('id');
            $table->string('applicant_name')->nullable()->after('client_id');
            $table->string('applicant_phone')->nullable()->after('applicant_name');
            $table->string('applicant_email')->nullable()->after('applicant_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('callback_requests');

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropColumn(['tracking_number', 'applicant_name', 'applicant_phone', 'applicant_email']);
        });
    }
};
