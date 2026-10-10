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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultancy_id')->constrained('consultancies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('name');
            // CNIC blind index hash (HMAC-SHA256) for unique & fast lookup without decrypting
            $table->string('cnic_hash', 64)->index();
            // Encrypted CNIC string
            $table->text('cnic');
            
            $table->string('phone', 30);
            $table->string('email')->nullable();
            $table->string('ntn', 30)->nullable();
            $table->string('father_name')->nullable();
            $table->text('address')->nullable();
            
            $table->string('status', 30)->default('active'); // active, pending_ntn, inactive
            $table->text('internal_notes')->nullable(); // Strictly internal for company eyes only
            
            $table->timestamps();
            
            // Unique CNIC hash per consultancy
            $table->unique(['consultancy_id', 'cnic_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
