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
        // 1. Dynamic Business Services Table
        if (!Schema::hasTable('business_services')) {
            Schema::create('business_services', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('category')->index(); // income, sales, company, intellectual, llc
                $table->string('category_name');
                $table->string('heading');
                $table->string('urdu')->nullable();
                $table->string('icon')->default('💼');
                $table->decimal('fee', 10, 2)->default(0.00);
                $table->string('currency', 10)->default('Rs.');
                $table->string('turnaround')->default('1 - 2 Working Days');
                $table->boolean('popular')->default(false);
                $table->boolean('is_active')->default(true)->index();
                $table->text('desc')->nullable();
                $table->json('requirements')->nullable();
                $table->json('form_fields')->nullable();
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 2. Dynamic FAQs Table
        if (!Schema::hasTable('faqs')) {
            Schema::create('faqs', function (Blueprint $table) {
                $table->id();
                $table->string('category')->default('all')->index();
                $table->string('question');
                $table->string('urdu_question')->nullable();
                $table->text('answer');
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        // 3. Add extra contact fields to consultancies table
        Schema::table('consultancies', function (Blueprint $table) {
            if (!Schema::hasColumn('consultancies', 'whatsapp')) {
                $table->string('whatsapp')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('consultancies', 'secondary_phone')) {
                $table->string('secondary_phone')->nullable()->after('whatsapp');
            }
            if (!Schema::hasColumn('consultancies', 'secondary_address')) {
                $table->text('secondary_address')->nullable()->after('address');
            }
            if (!Schema::hasColumn('consultancies', 'office_hours')) {
                $table->string('office_hours')->nullable()->after('secondary_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('business_services');

        Schema::table('consultancies', function (Blueprint $table) {
            $columns = ['whatsapp', 'secondary_phone', 'secondary_address', 'office_hours'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('consultancies', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
