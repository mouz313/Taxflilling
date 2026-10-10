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
        Schema::create('tax_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50); // e.g. "Tax Year 2024-2025"
            $table->date('start_date');
            $table->date('end_date');
            $table->date('filing_deadline');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tax_slabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_year_id')->constrained('tax_years')->cascadeOnDelete();
            $table->string('taxpayer_type', 30)->default('salaried'); // 'salaried', 'business'
            $table->decimal('min_income', 15, 2)->default(0);
            $table->decimal('max_income', 15, 2)->nullable(); // null represents above threshold (infinity)
            $table->decimal('fixed_tax', 15, 2)->default(0);
            $table->decimal('percentage_rate', 5, 2)->default(0); // e.g. 15.00%
            $table->decimal('exceeding_threshold', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_slabs');
        Schema::dropIfExists('tax_years');
    }
};
