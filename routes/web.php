<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CompanySettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaxReturnController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - TaxFiling Platform
|--------------------------------------------------------------------------
*/

// 1. Public Website & Landing Page (Befiler Style)
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Business Services Hub (All 5 Categories + Forms + Callbacks)
Route::get('/services', [\App\Http\Controllers\BusinessServicesController::class, 'index'])->name('services.index');
Route::get('/business-services', [\App\Http\Controllers\BusinessServicesController::class, 'index'])->name('business-services');
Route::post('/services/apply', [\App\Http\Controllers\BusinessServicesController::class, 'storeRequest'])->name('services.apply');
Route::get('/services/{code}/apply', function ($code) {
    return redirect()->route('services.index', ['apply' => $code]);
})->name('services.apply.code');
Route::post('/services/check-cnic', [\App\Http\Controllers\BusinessServicesController::class, 'checkCnic'])
    ->middleware('throttle:public-lookup')
    ->name('services.check-cnic');
Route::post('/services/callback', [\App\Http\Controllers\BusinessServicesController::class, 'storeCallback'])->name('services.callback');
Route::get('/services/success', [\App\Http\Controllers\BusinessServicesController::class, 'success'])->name('services.success');
Route::post('/services/{serviceRequest}/proof', [\App\Http\Controllers\BusinessServicesController::class, 'submitProof'])
    ->middleware('throttle:public-lookup')
    ->name('services.payment-proof');
Route::post('/services/{serviceRequest}/checkout', [\App\Http\Controllers\PaymentGatewayController::class, 'initiatePayment'])
    ->middleware('throttle:public-lookup')
    ->name('payment.checkout');
Route::post('/payment/callback/{gateway}', [\App\Http\Controllers\PaymentGatewayController::class, 'handleCallback'])
    ->where('gateway', 'jazzcash')
    ->middleware('throttle:payment-webhook')
    ->name('payment.callback');

// Online ATL Status Checker (Non-persisted Model Verification)
Route::post('/check-atl', [\App\Http\Controllers\AtlCheckerController::class, 'check'])
    ->middleware('throttle:public-lookup')
    ->name('atl.check');

// Public Track, FAQ & Contact Pages (Dynamic CMS with Safe Fallback)
Route::get('/track', [\App\Http\Controllers\TrackController::class, 'index'])
    ->middleware('throttle:public-lookup')
    ->name('track');
Route::get('/track/search', [\App\Http\Controllers\TrackController::class, 'search'])
    ->middleware('throttle:public-lookup')
    ->name('track.search');
Route::get('/faq', function () {
    $faqs = collect();
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('faqs')) {
            $faqs = \App\Models\Faq::where('is_active', true)->orderBy('sort_order')->get();
        }
    } catch (\Throwable $e) {
        $faqs = collect();
    }
    return view('faq', compact('faqs'));
})->name('faq');

Route::get('/contact', function () {
    $consultancy = null;
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('consultancies')) {
            $consultancy = \App\Models\Consultancy::first();
        }
    } catch (\Throwable $e) {
        $consultancy = null;
    }
    return view('contact', compact('consultancy'));
})->name('contact');

// Public Dynamic CMS Page Route
Route::get('/p/{slug}', [\App\Http\Controllers\PageViewController::class, 'show'])->name('pages.show');

// XML Sitemap for Search Engines (Google, Bing)
Route::get('/sitemap.xml', [\App\Http\Controllers\SitemapController::class, 'index'])->name('sitemap');

// 2. Authentication (Login & Self-Service Registration)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// 3. Authenticated Workspace
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Shared: 12-Category Self-Service Filing Wizard (Both Client & Company can access)
    Route::get('returns/{return}/wizard', [TaxReturnController::class, 'wizard'])->name('returns.wizard');
    Route::get('returns/{return}/filing', [\App\Http\Controllers\FilingWizardController::class, 'show'])->name('wizard.filing');
    Route::post('returns/{return}/filing/{step}', [\App\Http\Controllers\FilingWizardController::class, 'saveStep'])->name('wizard.filing.save');
    Route::post('returns/{return}/categories', [TaxReturnController::class, 'updateCategories'])->name('returns.categories');
    Route::post('returns/{return}/income', [TaxReturnController::class, 'addIncomeItem'])->name('returns.income');
    Route::delete('income-items/{item}', [TaxReturnController::class, 'removeIncomeItem'])->name('income-items.destroy');
    Route::post('returns/{return}/adjustment', [TaxReturnController::class, 'addAdjustment'])->name('returns.adjustment');
    Route::delete('adjustments/{adjustment}', [TaxReturnController::class, 'removeAdjustment'])->name('adjustments.destroy');
    Route::post('returns/{return}/deduction', [TaxReturnController::class, 'addDeduction'])->name('returns.deduction');
    Route::delete('deductions/{deduction}', [TaxReturnController::class, 'removeDeduction'])->name('deductions.destroy');
    Route::post('returns/{return}/expenses', [TaxReturnController::class, 'updateExpenses'])->name('returns.expenses');
    Route::post('returns/{return}/asset', [TaxReturnController::class, 'addAsset'])->name('returns.asset');
    Route::delete('assets/{asset}', [TaxReturnController::class, 'removeAsset'])->name('assets.destroy');
    Route::post('returns/{return}/liability', [TaxReturnController::class, 'addLiability'])->name('returns.liability');
    Route::delete('liabilities/{liability}', [TaxReturnController::class, 'removeLiability'])->name('liabilities.destroy');
    Route::post('returns/{return}/wealth', [TaxReturnController::class, 'updateWealth'])->name('returns.wealth');
    Route::post('returns/{return}/sync-fbr', [TaxReturnController::class, 'syncFbr'])->name('returns.sync-fbr');

    // Download Completed Deliverables / Certificates (Clients & Firm)
    Route::get('applications/{serviceRequest}/download', [\App\Http\Controllers\BusinessServicesController::class, 'downloadDeliverable'])->name('services.download');
    Route::get('returns/{return}/pdf', [\App\Http\Controllers\PdfDossierController::class, 'generateReturnDossier'])->name('returns.pdf');
    Route::get('services/{serviceRequest}/certificate-pdf', [\App\Http\Controllers\PdfDossierController::class, 'generateServiceCertificate'])->name('services.certificate.pdf');

    // Company Operations (Admin/Consultant Console)
    Route::middleware('role:company')->group(function () {
        // Clients Management
        Route::resource('clients', ClientController::class);

        // Tax Returns Pipeline
        Route::resource('returns', TaxReturnController::class);
        Route::post('returns/{return}/status', [TaxReturnController::class, 'transitionStatus'])->name('returns.status');
        Route::post('returns/{return}/confirm-fee', [TaxReturnController::class, 'confirmFee'])->name('returns.confirm-fee');

        // Dedicated Performance Boards: Corporate Orders & Call Requests
        Route::get('admin/corporate-orders', [\App\Http\Controllers\Admin\CorporateOrderController::class, 'index'])->name('admin.orders.index');
        Route::post('admin/corporate-orders/{serviceRequest}/status', [\App\Http\Controllers\Admin\CorporateOrderController::class, 'updateStatus'])->name('admin.orders.status');
        Route::post('admin/corporate-orders/{serviceRequest}/mark-paid', [\App\Http\Controllers\BusinessServicesController::class, 'markPaid'])->name('services.mark-paid');
        Route::post('services/{serviceRequest}/mark-paid', [\App\Http\Controllers\BusinessServicesController::class, 'markPaid']);
        Route::delete('admin/corporate-orders/{serviceRequest}', [\App\Http\Controllers\Admin\CorporateOrderController::class, 'destroy'])->name('admin.orders.destroy');
        Route::get('services/{serviceRequest}/proof-file', function (\App\Models\ServiceRequest $serviceRequest) {
            abort_unless($serviceRequest->payment_proof_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($serviceRequest->payment_proof_path), 404);
            return \Illuminate\Support\Facades\Storage::disk('local')->response($serviceRequest->payment_proof_path);
        })->name('services.proof-file');

        Route::get('admin/call-requests', [\App\Http\Controllers\Admin\CallRequestController::class, 'index'])->name('admin.callbacks.index');
        Route::post('admin/call-requests/{callback}/status', [\App\Http\Controllers\Admin\CallRequestController::class, 'updateStatus'])->name('admin.callbacks.status');

        // FBR IRIS 2.0 Live Verification Bridge
        Route::get('admin/fbr-verification', [\App\Http\Controllers\Admin\FbrVerificationController::class, 'index'])->name('admin.fbr.index');
        Route::post('admin/fbr-verification/verify', [\App\Http\Controllers\Admin\FbrVerificationController::class, 'verify'])->name('admin.fbr.verify');

        // Excel / CSV Export Reports (1-Click Streaming Downloads)
        Route::get('admin/corporate-orders/export', [\App\Http\Controllers\Admin\ReportExportController::class, 'exportOrders'])->name('admin.orders.export');
        Route::get('returns-export', [\App\Http\Controllers\Admin\ReportExportController::class, 'exportReturns'])->name('returns.export');
        Route::get('clients-export', [\App\Http\Controllers\Admin\ReportExportController::class, 'exportClients'])->name('clients.export');

        // Company Settings
        Route::get('settings/company', [CompanySettingsController::class, 'show'])->name('settings.company');
        Route::post('settings/company', [CompanySettingsController::class, 'update'])->name('settings.company.update');

        // CMS Content Management (Services, Categories, Pages, Menus, FAQs, Firm Profile)
        Route::prefix('admin/cms')->name('admin.cms.')->group(function () {
            // 1. Service Categories CMS
            Route::resource('categories', \App\Http\Controllers\Admin\ServiceCategoryController::class)->except(['show']);
            Route::post('categories/{category}/toggle', [\App\Http\Controllers\Admin\ServiceCategoryController::class, 'toggle'])->name('categories.toggle');

            // 2. Business Services CMS
            Route::resource('services', \App\Http\Controllers\Admin\CmsServiceController::class)->except(['show']);
            Route::post('services/{service}/toggle', [\App\Http\Controllers\Admin\CmsServiceController::class, 'toggle'])->name('services.toggle');

            // 3. Website Pages CMS (About Us, Terms, Privacy)
            Route::resource('pages', \App\Http\Controllers\Admin\PageController::class)->except(['show']);
            Route::post('pages/{page}/toggle', [\App\Http\Controllers\Admin\PageController::class, 'toggle'])->name('pages.toggle');

            // 4. Drag & Drop Navigation Menus CMS
            Route::get('menus', [\App\Http\Controllers\Admin\MenuController::class, 'index'])->name('menus.index');
            Route::post('menus', [\App\Http\Controllers\Admin\MenuController::class, 'store'])->name('menus.store');
            Route::put('menus/{menu}', [\App\Http\Controllers\Admin\MenuController::class, 'update'])->name('menus.update');
            Route::delete('menus/{menu}', [\App\Http\Controllers\Admin\MenuController::class, 'destroy'])->name('menus.destroy');
            Route::post('menus/reorder', [\App\Http\Controllers\Admin\MenuController::class, 'reorder'])->name('menus.reorder');

            // 5. FAQs CMS
            Route::resource('faqs', \App\Http\Controllers\Admin\CmsFaqController::class)->except(['show']);
            Route::post('faqs/{faq}/toggle', [\App\Http\Controllers\Admin\CmsFaqController::class, 'toggle'])->name('faqs.toggle');

            // 6. Website Content & Firm Profile
            Route::get('site', [\App\Http\Controllers\Admin\CmsSiteContentController::class, 'index'])->name('site.index');
            Route::post('site', [\App\Http\Controllers\Admin\CmsSiteContentController::class, 'update'])->name('site.update');

            // 7. SEO & Digital Marketing Management
            Route::get('seo', [\App\Http\Controllers\Admin\SeoMarketingController::class, 'index'])->name('seo.index');
            Route::post('seo', [\App\Http\Controllers\Admin\SeoMarketingController::class, 'update'])->name('seo.update');
        });
    });

    // Client Portal Specific Routes
    Route::middleware('role:client')->group(function () {
        Route::get('/my-returns', [TaxReturnController::class, 'clientIndex'])->name('client.returns');
        Route::get('/my-returns/{return}', [TaxReturnController::class, 'wizard'])->name('client.returns.show');
        Route::post('/client/returns/start', [DashboardController::class, 'startReturn'])->name('client.returns.start');
        Route::delete('/my-returns/{return}', [TaxReturnController::class, 'destroy'])->name('client.returns.destroy');
        Route::get('/my-applications', [\App\Http\Controllers\BusinessServicesController::class, 'clientApplications'])->name('client.applications');
    });
});
