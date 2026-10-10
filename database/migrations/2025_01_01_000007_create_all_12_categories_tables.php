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
        // Add selected_categories array and household_expenses to tax_returns
        Schema::table('tax_returns', function (Blueprint $table) {
            $table->json('selected_categories')->nullable()->after('filing_type'); // Array of active category keys e.g. ['salary', 'advance_tax', 'expenses']
            $table->decimal('household_expenses', 15, 2)->default(0)->after('tax_deducted_advance');
        });

        // 8. Advance / Withholding Taxes (Mobile, Token Tax, Electricity, Cash Withdrawal)
        Schema::create('tax_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_return_id')->constrained('tax_returns')->cascadeOnDelete();
            $table->string('adjustment_type', 40); // mobile, vehicle_token, electricity, bank_transaction, other
            $table->string('iris_box_code', 20)->default('6400');
            $table->string('reference_no')->nullable(); // Consumer ID / Registration / Phone
            $table->string('institution_name')->nullable(); // Jazz / LESCO / Excise / HBL
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->timestamps();
        });

        // 9. Tax Deductions & Credits (Zakat, Charity Sec 61, Pension Sec 63)
        Schema::create('tax_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_return_id')->constrained('tax_returns')->cascadeOnDelete();
            $table->string('deduction_type', 40); // zakat, charity_sec61, pension_sec63, education_sec60d
            $table->string('iris_box_code', 20)->default('9000');
            $table->string('institution_or_fund')->nullable();
            $table->decimal('eligible_amount', 15, 2)->default(0);
            $table->decimal('computed_credit_or_deduction', 15, 2)->default(0);
            $table->timestamps();
        });

        // 11. Wealth Assets (Properties, Vehicles, Bank Balances, Gold, Cash)
        Schema::create('wealth_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_return_id')->constrained('tax_returns')->cascadeOnDelete();
            $table->string('asset_type', 40); // property, vehicle, bank_account, gold, cash_in_hand, other
            $table->string('iris_box_code', 20)->default('7001');
            $table->string('title'); // e.g. "Plot # 45 DHA Phase 6" or "Toyota Corolla 2021"
            $table->text('details')->nullable();
            $table->decimal('cost_value', 15, 2)->default(0); // Purchase cost as per FBR rules
            $table->timestamps();
        });

        // 12. Wealth Liabilities (Bank Loans, Mortgages, Personal Borrowings)
        Schema::create('wealth_liabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_return_id')->constrained('tax_returns')->cascadeOnDelete();
            $table->string('liability_type', 40); // bank_loan, personal_loan, mortgage, other
            $table->string('iris_box_code', 20)->default('7015');
            $table->string('creditor_name'); // e.g. Meezan Bank Car Ijarah
            $table->decimal('outstanding_amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wealth_liabilities');
        Schema::dropIfExists('wealth_assets');
        Schema::dropIfExists('tax_deductions');
        Schema::dropIfExists('tax_adjustments');
        
        Schema::table('tax_returns', function (Blueprint $table) {
            $table->dropColumn(['selected_categories', 'household_expenses']);
        });
    }
};
