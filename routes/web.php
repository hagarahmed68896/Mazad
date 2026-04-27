<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Public Ad View — increments views_count
Route::get('/ads/{ad}', function(\App\Models\Ad $ad) {
    $ad->increment('views_count');
    // Redirect to app or return JSON for now (extend when ad show page is ready)
    return back();
})->name('ads.show');

Route::middleware('auth')->group(function() {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    
    // Location Update
    Route::post('/location/update', [\App\Http\Controllers\LocationController::class, 'update'])->name('location.update');
});


Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::get('register', [AuthController::class, 'showRegisterTypeForm'])->name('register.type');
Route::get('register/individual', [AuthController::class, 'showRegisterIndividualForm'])->name('register.individual');
Route::get('register/company', [AuthController::class, 'showRegisterCompanyForm'])->name('register.company');
Route::post('register', [AuthController::class, 'register'])->name('register.post');

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('post-login');

    Route::get('verify-otp', [AuthController::class, 'showVerifyOtpForm'])->name('verify-otp');
    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('post-verify');

    Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('post-forgot-password');

    Route::get('reset-password', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('post-reset-password');
    Route::get('reset-success', [AuthController::class, 'showResetSuccess'])->name('reset-success');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('verification-success', [AuthController::class, 'showVerificationSuccess'])->name('verification.success');
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Accounts Management
        Route::group(['prefix' => 'accounts', 'as' => 'accounts.'], function () {
            // Blocked Accounts
            Route::get('blocked', [\App\Http\Controllers\Admin\UserController::class, 'blockedAccounts'])->name('blocked-accounts');
            Route::post('blocked/bulk-toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'bulkToggleBlock'])->name('blocked.bulk-toggle-block');

            // Individuals
            Route::get('individuals', [\App\Http\Controllers\Admin\UserController::class, 'individuals'])->name('individuals');
            Route::get('individuals/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('individuals.create');
            Route::post('individuals/bulk-destroy', [\App\Http\Controllers\Admin\UserController::class, 'bulkDestroy'])->name('individuals.bulk-destroy');
            Route::post('individuals/bulk-toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'bulkToggleBlock'])->name('individuals.bulk-toggle-block');
            Route::get('individuals/download', [\App\Http\Controllers\Admin\UserController::class, 'download'])->name('individuals.download');
            Route::get('individuals/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('individuals.show');
            Route::post('individuals/{id}/toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'toggleBlock'])->name('individuals.toggle-block');
            Route::get('individuals/{id}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('individuals.edit');
            Route::delete('individuals/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('individuals.destroy');

            // Companies
            Route::get('companies', [\App\Http\Controllers\Admin\UserController::class, 'companies'])->name('companies');
            Route::post('companies/bulk-destroy', [\App\Http\Controllers\Admin\UserController::class, 'bulkDestroy'])->name('companies.bulk-destroy');
            Route::post('companies/bulk-toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'bulkToggleBlock'])->name('companies.bulk-toggle-block');
            Route::get('companies/download', [\App\Http\Controllers\Admin\UserController::class, 'companiesDownload'])->name('companies.download');
            Route::get('companies/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('companies.show');
            Route::post('companies/{id}/toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'toggleBlock'])->name('companies.toggle-block');

            // Partners (المندوبين)
            Route::get('partners', [\App\Http\Controllers\Admin\UserController::class, 'partners'])->name('partners');
            Route::post('partners/bulk-toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'bulkToggleBlock'])->name('partners.bulk-toggle-block');
            Route::get('partners/download', [\App\Http\Controllers\Admin\UserController::class, 'partnersDownload'])->name('partners.download');
            Route::get('partners/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('partners.show');
            Route::post('partners/{id}/toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'toggleBlock'])->name('partners.toggle-block');

            // Associates (الشركاء)
            Route::get('associates', [\App\Http\Controllers\Admin\UserController::class, 'associates'])->name('associates');
            Route::post('associates/bulk-toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'bulkToggleBlock'])->name('associates.bulk-toggle-block');
            Route::get('associates/download', [\App\Http\Controllers\Admin\UserController::class, 'associatesDownload'])->name('associates.download');
            Route::get('associates/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('associates.show');
            Route::post('associates/{id}/toggle-block', [\App\Http\Controllers\Admin\UserController::class, 'toggleBlock'])->name('associates.toggle-block');
            
            // Shared Order Actions
            Route::post('orders/{order}/send-code', [\App\Http\Controllers\Admin\UserController::class, 'sendOrderCode'])->name('orders.send-code');
        });

        // Ads Management
        Route::group(['prefix' => 'ads', 'as' => 'ads.'], function () {
            Route::get('incomplete', [\App\Http\Controllers\Admin\AdController::class, 'incomplete'])->name('incomplete');
            Route::get('incomplete/download', [\App\Http\Controllers\Admin\AdController::class, 'incompleteDownload'])->name('incomplete.download');
            Route::get('auctions', [\App\Http\Controllers\Admin\AdController::class, 'auctions'])->name('auctions');
            Route::get('auctions/download', [\App\Http\Controllers\Admin\AdController::class, 'auctionsDownload'])->name('auctions.download');
            Route::get('direct-purchase', [\App\Http\Controllers\Admin\AdController::class, 'directPurchase'])->name('direct_purchase');
            Route::get('direct-purchase/download', [\App\Http\Controllers\Admin\AdController::class, 'directPurchaseDownload'])->name('direct_purchase.download');
            Route::get('tenders', [\App\Http\Controllers\Admin\AdController::class, 'tenders'])->name('tenders');
            Route::get('tenders/download', [\App\Http\Controllers\Admin\AdController::class, 'tendersDownload'])->name('tenders.download');
            Route::get('services', [\App\Http\Controllers\Admin\AdController::class, 'services'])->name('services');
            Route::get('services/download', [\App\Http\Controllers\Admin\AdController::class, 'servicesDownload'])->name('services.download');
        });

        // Purchases Management
        Route::group(['prefix' => 'purchases', 'as' => 'purchases.'], function () {
            Route::get('/', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('index');
            Route::get('/download', [\App\Http\Controllers\Admin\OrderController::class, 'download'])->name('download');
        });

        // Payments Management
        Route::group(['prefix' => 'payments', 'as' => 'payments.'], function () {
            Route::get('/', [\App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('index');
            Route::get('/download', [\App\Http\Controllers\Admin\TransactionController::class, 'download'])->name('download');
            
            // Commissions
            Route::group(['prefix' => 'commissions', 'as' => 'commissions.'], function () {
                Route::get('/', [\App\Http\Controllers\Admin\CommissionController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\CommissionController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Admin\CommissionController::class, 'store'])->name('store');
                Route::get('/{commission}/edit', [\App\Http\Controllers\Admin\CommissionController::class, 'edit'])->name('edit');
                Route::put('/{commission}', [\App\Http\Controllers\Admin\CommissionController::class, 'update'])->name('update');
                Route::delete('/{commission}', [\App\Http\Controllers\Admin\CommissionController::class, 'destroy'])->name('destroy');
            });
            
            // Financial Settlements
            Route::group(['prefix' => 'settlements', 'as' => 'settlements.'], function () {
                Route::get('/', [\App\Http\Controllers\Admin\FinancialSettlementController::class, 'index'])->name('index');
                Route::get('/download', [\App\Http\Controllers\Admin\FinancialSettlementController::class, 'download'])->name('download');
                Route::put('/{settlement}/transfer', [\App\Http\Controllers\Admin\FinancialSettlementController::class, 'transfer'])->name('transfer');
            });
        });
        
        // Content Management: Categories
        Route::group(['prefix' => 'categories', 'as' => 'categories.'], function () {
            Route::get('/', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('store');
            Route::get('/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'show'])->name('show');
            Route::get('/{category}/edit', [\App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('destroy');

            // Properties nested under categories
            Route::group(['prefix' => '{category}/properties', 'as' => 'properties.'], function () {
                Route::get('/', [\App\Http\Controllers\Admin\PropertyController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\PropertyController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Admin\PropertyController::class, 'store'])->name('store');
                Route::post('/reorder', [\App\Http\Controllers\Admin\PropertyController::class, 'reorder'])->name('reorder');
                Route::get('/{property}/edit', [\App\Http\Controllers\Admin\PropertyController::class, 'edit'])->name('edit');
                Route::put('/{property}', [\App\Http\Controllers\Admin\PropertyController::class, 'update'])->name('update');
                Route::delete('/{property}', [\App\Http\Controllers\Admin\PropertyController::class, 'destroy'])->name('destroy');
            });
        });

        // Cities & Regions Management
        Route::group(['prefix' => 'cities', 'as' => 'cities.'], function () {
            Route::get('/', [\App\Http\Controllers\Admin\CityController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\CityController::class, 'store'])->name('store');
            Route::put('/{region}', [\App\Http\Controllers\Admin\CityController::class, 'update'])->name('update');
            Route::delete('/{region}', [\App\Http\Controllers\Admin\CityController::class, 'destroy'])->name('destroy');
            
            // Area Management
            Route::post('/{region}/areas', [\App\Http\Controllers\Admin\CityController::class, 'addArea'])->name('areas.store');
            Route::delete('/areas/{city}', [\App\Http\Controllers\Admin\CityController::class, 'destroyArea'])->name('areas.destroy');
        });

        // Profile
        Route::get('profile', [\App\Http\Controllers\Admin\ProfileController::class, 'index'])->name('profile.index');
        Route::post('profile/update', [\App\Http\Controllers\Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::post('profile/update-avatar', [\App\Http\Controllers\Admin\ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
        Route::post('profile/password', [\App\Http\Controllers\Admin\ProfileController::class, 'updatePassword'])->name('profile.password');

        // Settings
        Route::get('settings/contacts', [\App\Http\Controllers\Admin\SettingController::class, 'contacts'])->name('settings.contacts');
        Route::post('settings/contacts', [\App\Http\Controllers\Admin\SettingController::class, 'updateContacts'])->name('settings.contacts.update');
        Route::get('settings/ads', [\App\Http\Controllers\Admin\SettingController::class, 'ads'])->name('settings.ads');
        Route::post('settings/ads', [\App\Http\Controllers\Admin\SettingController::class, 'updateAds'])->name('settings.ads.update');
        Route::get('settings/financial', [\App\Http\Controllers\Admin\SettingController::class, 'financial'])->name('settings.financial');
        Route::post('settings/financial', [\App\Http\Controllers\Admin\SettingController::class, 'updateFinancial'])->name('settings.financial.update');
        
        // Policies
        Route::post('policies/bulk-destroy', [\App\Http\Controllers\Admin\PolicyController::class, 'bulkDestroy'])->name('policies.bulk-destroy');
        Route::resource('policies', \App\Http\Controllers\Admin\PolicyController::class);

        // FAQs
        Route::get('faqs/download', [\App\Http\Controllers\Admin\FaqController::class, 'download'])->name('faqs.download');
        Route::post('faqs/bulk-destroy', [\App\Http\Controllers\Admin\FaqController::class, 'bulkDestroy'])->name('faqs.bulk-destroy');
        Route::resource('faqs', \App\Http\Controllers\Admin\FaqController::class);

        // Tickets
        Route::get('tickets/download', [\App\Http\Controllers\Admin\TicketController::class, 'download'])->name('tickets.download');
        Route::post('tickets/bulk-destroy', [\App\Http\Controllers\Admin\TicketController::class, 'bulkDestroy'])->name('tickets.bulk-destroy');
        Route::resource('tickets', \App\Http\Controllers\Admin\TicketController::class);

        // Banners Management
        Route::resource('banners', \App\Http\Controllers\Admin\BannerController::class);

        // Reviews Management
        Route::get('reviews', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('reviews.index');
        Route::get('reviews-download', [\App\Http\Controllers\Admin\ReviewController::class, 'download'])->name('reviews.download');
        // Notifications Management
        Route::post('notifications/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
        Route::post('notifications/{notification}/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('notifications.mark-read');
        Route::post('notifications/{notification}/send', [\App\Http\Controllers\Admin\NotificationController::class, 'send'])->name('notifications.send');
        Route::post('notifications/bulk-destroy', [\App\Http\Controllers\Admin\NotificationController::class, 'bulkDestroy'])->name('notifications.bulk-destroy');
        Route::resource('notifications', \App\Http\Controllers\Admin\NotificationController::class);
        
        // Reports & Analytics
        Route::get('reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('reports.index');
    });
});
