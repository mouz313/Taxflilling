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
        Schema::create('consultancies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('ntn_registration_no')->nullable();
            $table->string('logo_path')->nullable();
            
            // Default Fee Settings
            $table->decimal('default_return_fee', 10, 2)->default(2500.00);
            $table->decimal('default_ntn_fee', 10, 2)->default(1500.00);
            $table->decimal('default_wealth_statement_fee', 10, 2)->default(3000.00);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultancies');
    }
};
