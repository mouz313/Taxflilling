<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'profile_photo_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('profile_photo_path')->nullable()->after('phone');
            });
        }

        if (!Schema::hasColumn('consultancies', 'logo_path')) {
            Schema::table('consultancies', function (Blueprint $table) {
                $table->string('logo_path')->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'profile_photo_path')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('profile_photo_path');
            });
        }

        if (Schema::hasColumn('consultancies', 'logo_path')) {
            Schema::table('consultancies', function (Blueprint $table) {
                $table->dropColumn('logo_path');
            });
        }
    }
};
