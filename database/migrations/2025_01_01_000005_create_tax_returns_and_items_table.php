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
        Schema::create('tax_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultancy_id')->constrained('consultancies')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('tax_year_id')->constrained('tax_years')->cascadeOnDelete();
            
            // Workflow Status State Machine
            $table->string('status', 30)->default('draft')->index(); 
            // draft, docs_pending, in_review, query, ready_to_file, filed, closed
            
            $table->string('filing_type', 20)->default('original'); // original, amendment
            $table->unsignedInteger('amendment_no')->default(0);
            $table->boolean('is_locked')->default(false); // Locked once filed on IRIS
            
            // Financial Summaries & Tax Engine Totals
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('taxable_income', 15, 2)->default(0);
            $table->decimal('computed_tax', 15, 2)->default(0);
            $table->decimal('tax_credits', 15, 2)->default(0);
            $table->decimal('tax_deducted_advance', 15, 2)->default(0); // Withholding / Advance
            $table->decimal('net_tax_payable_or_refundable', 15, 2)->default(0); // (+) Payable, (-) Refundable
            
            // FBR IRIS Filing Details
            $table->string('iris_acknowledgement_no')->nullable();
            $table->string('iris_cpr_no')->nullable();
            $table->timestamp('filed_at')->nullable();
            $table->foreignId('filed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Fee and Billing
            $table->decimal('fee_amount', 10, 2)->default(2500.00);
            $table->string('fee_status', 20)->default('pending'); // pending, paid
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            
            // Unique return per client per tax year per amendment
            $table->unique(['client_id', 'tax_year_id', 'amendment_no']);
        });

        Schema::create('income_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_return_id')->constrained('tax_returns')->cascadeOnDelete();
            $table->string('source_type', 30); // salary, bank_profit, rental, business, etc.
            $table->string('iris_box_code', 20)->nullable(); // e.g. 7001, 7003, 640101
            $table->string('employer_or_institution')->nullable();
            $table->text('description')->nullable();
            $table->decimal('gross_amount', 15, 2)->default(0);
            $table->decimal('exempt_amount', 15, 2)->default(0);
            $table->decimal('taxable_amount', 15, 2)->default(0);
            $table->decimal('tax_deducted', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('wealth_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_return_id')->constrained('tax_returns')->cascadeOnDelete();
            $table->decimal('opening_wealth', 15, 2)->default(0);
            $table->decimal('net_inflows', 15, 2)->default(0);
            $table->decimal('living_expenses', 15, 2)->default(0);
            $table->decimal('other_outflows', 15, 2)->default(0);
            $table->decimal('closing_wealth', 15, 2)->default(0);
            $table->decimal('unreconciled_difference', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wealth_reconciliations');
        Schema::dropIfExists('income_items');
        Schema::dropIfExists('tax_returns');
    }
};
