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
        // 1. Dynamic Service Categories Table
        if (!Schema::hasTable('service_categories')) {
            Schema::create('service_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->string('icon')->default('📁');
                $table->text('description')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        // 2. Dynamic Custom Pages Table
        if (!Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->longText('content');
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->boolean('show_in_header')->default(false)->index();
                $table->boolean('show_in_footer')->default(true)->index();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        // 3. Dynamic Drag & Drop Navigation Menus Table
        if (!Schema::hasTable('menu_items')) {
            Schema::create('menu_items', function (Blueprint $table) {
                $table->id();
                $table->string('location')->default('header')->index(); // header, footer
                $table->string('title');
                $table->string('url');
                $table->string('target', 20)->default('_self'); // _self, _blank
                $table->foreignId('parent_id')->nullable()->constrained('menu_items')->nullOnDelete();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        // 4. Central App Settings Key-Value Table (Branding, Gateways, Email, Firebase)
        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->longText('value')->nullable();
                $table->string('group')->default('general')->index(); // general, branding, payment, mail, firebase
                $table->timestamps();
            });
        }

        // 5. Connect business_services with service_categories
        Schema::table('business_services', function (Blueprint $table) {
            if (!Schema::hasColumn('business_services', 'service_category_id')) {
                $table->foreignId('service_category_id')->nullable()->after('category')->constrained('service_categories')->nullOnDelete();
            }
        });

        // 6. Add payment proof and structured file uploads to service_requests
        Schema::table('service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('service_requests', 'business_service_id')) {
                $table->foreignId('business_service_id')->nullable()->after('client_id')->constrained('business_services')->nullOnDelete();
            }
            if (!Schema::hasColumn('service_requests', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable()->after('payment_reference');
            }
            if (!Schema::hasColumn('service_requests', 'uploaded_files')) {
                $table->json('uploaded_files')->nullable()->after('data_payload');
            }
            if (!Schema::hasColumn('service_requests', 'applicant_cnic')) {
                $table->string('applicant_cnic', 50)->nullable()->after('filer_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'business_service_id')) {
                $table->dropForeign(['business_service_id']);
                $table->dropColumn('business_service_id');
            }
            if (Schema::hasColumn('service_requests', 'payment_proof_path')) {
                $table->dropColumn('payment_proof_path');
            }
            if (Schema::hasColumn('service_requests', 'uploaded_files')) {
                $table->dropColumn('uploaded_files');
            }
            if (Schema::hasColumn('service_requests', 'applicant_cnic')) {
                $table->dropColumn('applicant_cnic');
            }
        });

        Schema::table('business_services', function (Blueprint $table) {
            if (Schema::hasColumn('business_services', 'service_category_id')) {
                $table->dropForeign(['service_category_id']);
                $table->dropColumn('service_category_id');
            }
        });

        Schema::dropIfExists('app_settings');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('service_categories');
    }
};
