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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('consultancy_id')->nullable()->constrained('consultancies')->nullOnDelete()->after('id');
            $table->string('role', 20)->default('client')->index()->after('email'); // 'company', 'client'
            $table->string('phone', 30)->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['consultancy_id']);
            $table->dropColumn(['consultancy_id', 'role', 'phone', 'is_active']);
        });
    }
};
