<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/search', [\App\Http\Controllers\HomeController::class, 'search'])->name('search');
Route::get('/categories', [\App\Http\Controllers\HomeController::class, 'showAllCategories'])->name('categories.all');
Route::get('/categories/{id}', [\App\Http\Controllers\HomeController::class, 'showCategoryAds'])->name('categories.show');

// Ad Creation
Route::middleware('auth')->group(function() {
    Route::get('/my-ads', [\App\Http\Controllers\AdController::class, 'myAds'])->name('ads.my');
    Route::get('/my-ads/promote', [\App\Http\Controllers\AdController::class, 'promotePage'])->name('ads.promote-page');
    Route::post('/my-ads/promote/process', [\App\Http\Controllers\AdController::class, 'processBulkPromotion'])->name('ads.promote-bulk');
    Route::get('/ads/create', [\App\Http\Controllers\AdController::class, 'create'])->name('ads.create');
    Route::get('/ads/create/details', [\App\Http\Controllers\AdController::class, 'createDetails'])->name('ads.create.details');
    Route::post('/ads', [\App\Http\Controllers\AdController::class, 'store'])->name('ads.store');
    Route::get('/ads/{ad}/edit', [\App\Http\Controllers\AdController::class, 'edit'])->name('ads.edit');
    Route::put('/ads/{ad}', [\App\Http\Controllers\AdController::class, 'update'])->name('ads.update');
    Route::delete('/ads/{ad}', [\App\Http\Controllers\AdController::class, 'destroy'])->name('ads.destroy');
    Route::get('/ads/{ad}/payment', [\App\Http\Controllers\AdController::class, 'showPayment'])->name('ads.payment');
    Route::post('/ads/{ad}/payment/process', [\App\Http\Controllers\AdController::class, 'processPayment'])->name('ads.payment.process');

    // AJAX Ad Image Management Routes
    Route::post('/ads/{ad}/set-main-image', [\App\Http\Controllers\AdController::class, 'setMainImage'])->name('ads.set-main');
    Route::delete('/ads/images/{adImage}', [\App\Http\Controllers\AdController::class, 'deleteImage'])->name('ads.images.delete');
    Route::delete('/ads/{ad}/delete-main-image', [\App\Http\Controllers\AdController::class, 'deleteMainImage'])->name('ads.delete-main');
    Route::post('/ads/{ad}/repost', [\App\Http\Controllers\AdController::class, 'repost'])->name('ads.repost');
});

// Public Ad View — increments views_count
Route::get('/ads/{id}', [\App\Http\Controllers\HomeController::class, 'showAd'])->name('ads.show');
Route::get('/ads/{id}/services', [\App\Http\Controllers\HomeController::class, 'adServices'])->name('ads.services');
Route::get('/services/{id}', [\App\Http\Controllers\HomeController::class, 'serviceShow'])->name('services.show');
Route::get('/ads/{id}/gallery/{index?}', [\App\Http\Controllers\HomeController::class, 'showGallery'])->name('ads.gallery');
Route::get('/services/{id}/gallery/{index?}', [\App\Http\Controllers\HomeController::class, 'serviceGallery'])->name('services.gallery');
Route::get('/users/{id}/profile', [\App\Http\Controllers\HomeController::class, 'showProfile'])->name('users.profile');
Route::post('/ads/{id}/bid', [\App\Http\Controllers\BidController::class, 'store'])->name('bids.store')->middleware('auth');
Route::post('/ads/{id}/order', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store')->middleware('auth');

// Location Update
Route::post('/location/update', [\App\Http\Controllers\LocationController::class, 'update'])->name('location.update');
Route::get('/location/clear', [\App\Http\Controllers\LocationController::class, 'clear'])->name('location.clear');

Route::middleware('auth')->group(function() {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::post('/update-interests', [AuthController::class, 'updateInterests'])->name('update-interests.web');
    Route::post('/skip-interests', [AuthController::class, 'skipInterests'])->name('skip-interests.web');
    Route::get('/wishlist', [\App\Http\Controllers\WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle/{adId}', [\App\Http\Controllers\WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/look-later', [\App\Http\Controllers\LookLaterController::class, 'index'])->name('look-later.index');
    Route::post('/look-later/toggle/{adId}', [\App\Http\Controllers\LookLaterController::class, 'toggle'])->name('look-later.toggle');
    Route::get('/my-sales', [\App\Http\Controllers\OrderController::class, 'mySales'])->name('sales.my');
    Route::get('/my-purchases', [\App\Http\Controllers\OrderController::class, 'myPurchases'])->name('purchases.my');
    Route::get('/bids-offers', [\App\Http\Controllers\BidController::class, 'myBidsOffers'])->name('bids-offers.index');
    Route::post('/bids-offers/{bidId}/accept', [\App\Http\Controllers\BidController::class, 'acceptBidOffer'])->name('bids-offers.accept');
    Route::post('/bids-offers/{bidId}/reject', [\App\Http\Controllers\BidController::class, 'rejectBidOffer'])->name('bids-offers.reject');
    Route::post('/bids-offers/{bidId}/counter', [\App\Http\Controllers\BidController::class, 'counterBidOffer'])->name('bids-offers.counter');
    Route::get('/bids-offers/{bidId}/details', [\App\Http\Controllers\BidController::class, 'getDetails'])->name('bids-offers.details');
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/ads/{id}/bids-history', [\App\Http\Controllers\BidController::class, 'historyPage'])->name('bids.history-page');
    Route::post('/my-sales/seed', [\App\Http\Controllers\OrderController::class, 'seedMySales'])->name('sales.seed');
    Route::post('/my-sales/bulk-mark-sold', [\App\Http\Controllers\OrderController::class, 'bulkMarkSold'])->name('sales.bulk-mark-sold');
    Route::post('/orders/{order}/confirm-delivery', [\App\Http\Controllers\OrderController::class, 'confirmDelivery'])->name('orders.confirm-delivery');
    Route::post('/ads/{id}/question', [\App\Http\Controllers\AdQuestionController::class, 'store'])->name('ads.question.store');
    Route::post('/questions/{id}/reply', [\App\Http\Controllers\AdQuestionController::class, 'reply'])->name('questions.reply');
    Route::post('/questions/{id}/toggle-visibility', [\App\Http\Controllers\AdQuestionController::class, 'toggleVisibility'])->name('questions.toggle-visibility');
    Route::post('/users/{id}/report', [\App\Http\Controllers\HomeController::class, 'reportUser'])->name('users.report');
    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store'])->name('reviews.store');
    Route::post('/reviews/{id}/reply', [\App\Http\Controllers\ReviewController::class, 'reply'])->name('reviews.reply');
    Route::post('/reviews/{id}/toggle-visibility', [\App\Http\Controllers\ReviewController::class, 'toggleVisibility'])->name('reviews.toggle-visibility');
    Route::get('/my-reviews', [\App\Http\Controllers\ReviewController::class, 'myReviews'])->name('reviews.my');
    Route::get('/chats', [\App\Http\Controllers\ChatController::class, 'index'])->name('chats.index');
    Route::post('/chats/start', [\App\Http\Controllers\ChatController::class, 'startConversation'])->name('chats.start');
    Route::post('/chats/{id}/message', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chats.send');
    Route::delete('/chats/{id}', [\App\Http\Controllers\ChatController::class, 'destroy'])->name('chats.destroy');
    Route::post('/chats/{id}/toggle-pin', [\App\Http\Controllers\ChatController::class, 'togglePin'])->name('chats.pin');
    Route::post('/chats/{id}/toggle-mute', [\App\Http\Controllers\ChatController::class, 'toggleMute'])->name('chats.mute');
    Route::post('/users/{id}/toggle-block', [\App\Http\Controllers\ChatController::class, 'toggleBlock'])->name('users.block');



    // Profile Settings Routes
    Route::get('/settings/profile', [\App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/settings/profile', [\App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::post('/settings/avatar', [\App\Http\Controllers\ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::put('/settings/bank', [\App\Http\Controllers\ProfileController::class, 'updateBank'])->name('profile.bank.update');
    Route::put('/settings/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/settings/interests', [\App\Http\Controllers\ProfileController::class, 'interestsIndex'])->name('profile.interests.index');
    Route::put('/settings/interests', [\App\Http\Controllers\ProfileController::class, 'updateInterests'])->name('profile.interests.update');
    Route::get('/settings/notifications', [\App\Http\Controllers\ProfileController::class, 'notificationsIndex'])->name('profile.notifications.index');
    Route::put('/settings/notifications', [\App\Http\Controllers\ProfileController::class, 'updateNotifications'])->name('profile.notifications.update');

    // Cart Routes
    Route::get('/cart', [\App\Http\Controllers\CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [\App\Http\Controllers\CartController::class, 'store'])->name('cart.add');
    Route::patch('/cart/update/{id}', [\App\Http\Controllers\CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [\App\Http\Controllers\CartController::class, 'destroy'])->name('cart.remove');
    Route::post('/cart/checkout', [\App\Http\Controllers\CartController::class, 'checkout'])->name('cart.checkout');

    // Team Management Routes (Company Only)
    Route::get('/settings/team', [\App\Http\Controllers\TeamController::class, 'index'])->name('team.index');
    Route::get('/settings/team/create', [\App\Http\Controllers\TeamController::class, 'create'])->name('team.create');
    Route::post('/settings/team', [\App\Http\Controllers\TeamController::class, 'store'])->name('team.store');
    Route::delete('/settings/team/{id}', [\App\Http\Controllers\TeamController::class, 'destroy'])->name('team.destroy');
    Route::get('/settings/team/{id}/edit', [\App\Http\Controllers\TeamController::class, 'edit'])->name('team.edit');
    Route::put('/settings/team/{id}', [\App\Http\Controllers\TeamController::class, 'update'])->name('team.update');
});


// Overriding default broadcasting auth route to support API/Sanctum authentication for mobile clients without CSRF
Route::post('/broadcasting/auth', [\Illuminate\Broadcasting\BroadcastController::class, 'authenticate'])
    ->middleware(['api', 'auth:sanctum']);

// Support and Policies Pages
Route::get('/faqs', [\App\Http\Controllers\SupportController::class, 'faqs'])->name('faqs');
Route::get('/privacy-policy', [\App\Http\Controllers\SupportController::class, 'privacy'])->name('privacy');
Route::get('/terms-conditions', [\App\Http\Controllers\SupportController::class, 'terms'])->name('terms');
Route::get('/contact-us', [\App\Http\Controllers\SupportController::class, 'contactUs'])->name('contact');
Route::post('/contact-us', [\App\Http\Controllers\SupportController::class, 'storeTicket'])->name('contact.store');

Route::middleware('auth')->group(function() {
    Route::get('/my-inquiries', [\App\Http\Controllers\SupportController::class, 'myInquiries'])->name('inquiries.my');
});

// Public Auth Routes
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::get('verify-otp', [AuthController::class, 'showVerifyOtpForm'])->name('verify-otp');
Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('post-verify');
Route::get('verification-success', [AuthController::class, 'showVerificationSuccess'])->name('verification.success');

Route::get('register', [AuthController::class, 'showRegisterTypeForm'])->name('register.type');
Route::get('register/individual', [AuthController::class, 'showRegisterIndividualForm'])->name('register.individual');
Route::get('register/company', [AuthController::class, 'showRegisterCompanyForm'])->name('register.company');
Route::get('register/partner', [AuthController::class, 'showRegisterPartnerForm'])->name('register.partner');
Route::post('register', [AuthController::class, 'register'])->name('register.post');

Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('post-forgot-password');
Route::get('reset-password', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('post-reset-password');
Route::get('reset-success', [AuthController::class, 'showResetSuccess'])->name('reset-success');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
Route::post('save-services', [AuthController::class, 'saveServices'])->name('save-services.web')->middleware('auth');
Route::get('register/partner/interests', [AuthController::class, 'showPartnerInterestsForm'])->name('register.partner.interests')->middleware('auth');
Route::post('register/partner/interests', [AuthController::class, 'savePartnerInterests'])->name('register.partner.interests.post')->middleware('auth');
Route::get('register/partner/services', [AuthController::class, 'showPartnerServicesForm'])->name('register.partner.services')->middleware('auth');
Route::post('register/partner/services', [AuthController::class, 'savePartnerServices'])->name('register.partner.services.post')->middleware('auth');

// Admin Auth & Dashboard Routes
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('login', [\App\Http\Controllers\Admin\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('post-login');

    Route::get('verify-otp', [\App\Http\Controllers\Admin\AuthController::class, 'showVerifyOtpForm'])->name('verify-otp');
    Route::post('verify-otp', [\App\Http\Controllers\Admin\AuthController::class, 'verifyOtp'])->name('post-verify');

    Route::get('forgot-password', [\App\Http\Controllers\Admin\AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
    Route::post('forgot-password', [\App\Http\Controllers\Admin\AuthController::class, 'sendResetLink'])->name('post-forgot-password');

    Route::get('reset-password', [\App\Http\Controllers\Admin\AuthController::class, 'showResetPasswordForm'])->name('reset-password');
    Route::post('reset-password', [\App\Http\Controllers\Admin\AuthController::class, 'resetPassword'])->name('post-reset-password');
    Route::get('reset-success', [\App\Http\Controllers\Admin\AuthController::class, 'showResetSuccess'])->name('reset-success');

    Route::post('logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('verification-success', [\App\Http\Controllers\Admin\AuthController::class, 'showVerificationSuccess'])->name('verification.success');
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

Route::get('/seed-sales-data', function() {
    // 1. Find or create seller
    $seller = \App\Models\User::where('phone', 'like', '%512345678')->first() 
           ?? \App\Models\User::where('phone', 'like', '%0512345678%')->first();
           
    if (!$seller) {
        $seller = \App\Models\User::create([
            'name' => 'البائع المميز',
            'email' => 'seller5123@mazad.com',
            'phone' => '0512345678',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'account_type' => 'individual',
            'status' => 'active',
            'city_id' => \App\Models\City::first()->id ?? null,
        ]);
    }

    // 2. Find or create buyer
    $buyer = \App\Models\User::where('email', 'buyer5123@mazad.com')->first();
    if (!$buyer) {
        $buyer = \App\Models\User::create([
            'name' => 'أحمد المشتري',
            'email' => 'buyer5123@mazad.com',
            'phone' => '0599999999',
            'password' => \Illuminate\Support\Facades\Hash::make('123456'),
            'account_type' => 'individual',
            'status' => 'active',
            'city_id' => \App\Models\City::first()->id ?? null,
        ]);
    }

    $categories = \App\Models\Category::whereNotNull('parent_id')->get();
    if ($categories->isEmpty()) {
        $categories = \App\Models\Category::all();
    }
    $cities = \App\Models\City::all();
    
    // Get existing images from database
    $images = \App\Models\Ad::whereNotNull('image')->limit(12)->pluck('image')->toArray();

    // Clean up existing ads of this seller to avoid cluttering
    $seller->ads()->delete();

    // Array of mock data items for each of the 6 states
    $paidItems = [
        ['title' => 'لابتوب ديل XPS 15 شاشة تعمل باللمس', 'price' => 33000, 'type' => 'direct_purchase'],
        ['title' => 'كاميرا سوني Alpha 7 IV الاحترافية', 'price' => 9500, 'type' => 'direct_purchase'],
        ['title' => 'ساعة آبل الترا الإصدار الثاني رياضية', 'price' => 3200, 'type' => 'direct_purchase'],
        ['title' => 'سماعات دي جي بوس اللاسلكية عازلة للضوضاء', 'price' => 1500, 'type' => 'direct_purchase']
    ];

    $pendingPaymentItems = [
        ['title' => 'برج سكني وتجاري استثماري في الرياض', 'price' => 7000000, 'type' => 'electronic_auction'],
        ['title' => 'شقة فاخرة إطلالة بحرية كاملة في جدة', 'price' => 850000, 'type' => 'electronic_auction'],
        ['title' => 'أرض تجارية على طريق الملك فهد بالرياض', 'price' => 12000000, 'type' => 'electronic_auction'],
        ['title' => 'مجمع سكني مغلق حديث شمال العاصمة', 'price' => 25000000, 'type' => 'electronic_auction']
    ];

    $deliveringItems = [
        ['title' => 'لابتوب لينوفو ثينك باد X1 كاربون', 'price' => 10000, 'type' => 'direct_purchase'],
        ['title' => 'جهاز آيباد برو شريحة M2 شاشة 12.9', 'price' => 5400, 'type' => 'direct_purchase'],
        ['title' => 'نظام صوتي منزلي متكامل من ياماها', 'price' => 3800, 'type' => 'direct_purchase'],
        ['title' => 'آلة قهوة بريفيل باريستا إكسبريس الاحترافية', 'price' => 2900, 'type' => 'direct_purchase']
    ];

    $receivedItems = [
        ['title' => 'سيارة بي إم دبليو M4 كوبيه رياضية', 'price' => 2800000, 'type' => 'hybrid_auction'],
        ['title' => 'سيارة مرسيدس C200 موديل 2024 فل كامل', 'price' => 240000, 'type' => 'hybrid_auction'],
        ['title' => 'دراجة نارية رياضية ياماها R6 ياباني', 'price' => 45000, 'type' => 'hybrid_auction'],
        ['title' => 'سيارة تسلا موديل 3 فل كامل كهربائية', 'price' => 180000, 'type' => 'hybrid_auction']
    ];

    $expiredItems = [
        ['title' => 'فيلا سكنية مودرن مع مسبح خاص بحطين', 'price' => 5000000, 'type' => 'electronic_auction'],
        ['title' => 'شاليه فاخر حي الرمال مساحات واسعة', 'price' => 1200000, 'type' => 'electronic_auction'],
        ['title' => 'استراحة خاصة مجهزة بالكامل بالدرعية', 'price' => 3500000, 'type' => 'electronic_auction'],
        ['title' => 'مزرعة منتجة بوسط القصيم مساحة كبرى', 'price' => 8000000, 'type' => 'electronic_auction']
    ];

    $activeItems = [
        ['title' => 'هاتف آيفون 15 برو ماكس 512 جيجا', 'price' => 4500, 'type' => 'direct_purchase'],
        ['title' => 'شاشة إل جي OLED 65 بوصة ذكية 4K', 'price' => 6200, 'type' => 'direct_purchase'],
        ['title' => 'منصة ألعاب بلايستيشن 5 مع ذراعين ولعبة', 'price' => 2100, 'type' => 'direct_purchase'],
        ['title' => 'نظارة أبل فيجن برو للواقع المختلط الجديدة', 'price' => 14500, 'type' => 'direct_purchase']
    ];

    $adIndex = 0;

    // Create 4 Paid Ads & Orders
    foreach ($paidItems as $index => $item) {
        $img = $images[$adIndex % count($images)] ?? null;
        $adIndex++;
        $ad = \App\Models\Ad::create([
            'user_id' => $seller->id,
            'category_id' => $categories->random()->id ?? null,
            'city_id' => $cities->random()->id ?? null,
            'title' => $item['title'],
            'type' => $item['type'],
            'price' => $item['price'],
            'status' => 'finished',
            'image' => $img,
            'end_date' => now()->subDays($index + 1),
        ]);
        \App\Models\Order::create([
            'order_number' => 'ORD-PAID-' . ($index + 1),
            'ad_id' => $ad->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'final_price' => $item['price'] + 519,
            'status' => 'paid',
        ]);
    }

    // Create 4 Pending Payment Ads & Orders
    foreach ($pendingPaymentItems as $index => $item) {
        $img = $images[$adIndex % count($images)] ?? null;
        $adIndex++;
        $ad = \App\Models\Ad::create([
            'user_id' => $seller->id,
            'category_id' => $categories->random()->id ?? null,
            'city_id' => $cities->random()->id ?? null,
            'title' => $item['title'],
            'type' => $item['type'],
            'price' => $item['price'],
            'status' => 'finished',
            'image' => $img,
            'end_date' => now()->subDays($index + 1),
        ]);
        \App\Models\Order::create([
            'order_number' => 'ORD-PEND-' . ($index + 1),
            'ad_id' => $ad->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'final_price' => $item['price'] + 519,
            'status' => 'pending_payment',
        ]);
    }

    // Create 4 Delivering Ads & Orders (Confirm Delivery)
    foreach ($deliveringItems as $index => $item) {
        $img = $images[$adIndex % count($images)] ?? null;
        $adIndex++;
        $ad = \App\Models\Ad::create([
            'user_id' => $seller->id,
            'category_id' => $categories->random()->id ?? null,
            'city_id' => $cities->random()->id ?? null,
            'title' => $item['title'],
            'type' => $item['type'],
            'price' => $item['price'],
            'status' => 'finished',
            'image' => $img,
            'end_date' => now()->subDays($index + 1),
        ]);
        \App\Models\Order::create([
            'order_number' => 'ORD-RECV-' . ($index + 1),
            'ad_id' => $ad->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'final_price' => $item['price'] + 519,
            'status' => 'receiving',
            'delivery_code' => '123456',
        ]);
    }

    // Create 4 Received Ads & Orders
    foreach ($receivedItems as $index => $item) {
        $img = $images[$adIndex % count($images)] ?? null;
        $adIndex++;
        $ad = \App\Models\Ad::create([
            'user_id' => $seller->id,
            'category_id' => $categories->random()->id ?? null,
            'city_id' => $cities->random()->id ?? null,
            'title' => $item['title'],
            'type' => $item['type'],
            'price' => $item['price'],
            'status' => 'finished',
            'image' => $img,
            'end_date' => now()->subDays($index + 1),
        ]);
        \App\Models\Order::create([
            'order_number' => 'ORD-RCVD-' . ($index + 1),
            'ad_id' => $ad->id,
            'buyer_id' => $buyer->id,
            'seller_id' => $seller->id,
            'final_price' => $item['price'] + 519,
            'status' => 'received',
        ]);
    }

    // Create 4 Expired Unsold Ads (منتهي - إعادة بيع / تعديل)
    foreach ($expiredItems as $index => $item) {
        $img = $images[$adIndex % count($images)] ?? null;
        $adIndex++;
        \App\Models\Ad::create([
            'user_id' => $seller->id,
            'category_id' => $categories->random()->id ?? null,
            'city_id' => $cities->random()->id ?? null,
            'title' => $item['title'],
            'type' => $item['type'],
            'price' => $item['price'],
            'status' => 'finished',
            'image' => $img,
            'end_date' => now()->subDays($index + 2),
        ]);
    }

    // Create 4 Active Unsold Ads (قائم - تعديل)
    foreach ($activeItems as $index => $item) {
        $img = $images[$adIndex % count($images)] ?? null;
        $adIndex++;
        \App\Models\Ad::create([
            'user_id' => $seller->id,
            'category_id' => $categories->random()->id ?? null,
            'city_id' => $cities->random()->id ?? null,
            'title' => $item['title'],
            'type' => $item['type'],
            'price' => $item['price'],
            'status' => 'active',
            'image' => $img,
            'end_date' => now()->addDays($index + 3),
        ]);
    }

    return "<h2>تم إضافة البيانات التجريبية الغنية بنجاح للبائع (0512345678)! 🎉</h2>
            <p>تم إنشاء 24 إعلاناً متميزاً بكافة الحالات (4 إعلانات لكل حالة من الحالات الستة: تم الدفع، قيد الدفع، قيد الاستلام بكود 123456، تم الاستلام، منتهي، قائم).</p>
            <p>الآن يمكنك استعراض الصفحات، تجربة الفلترة، التنقل بين التبويبات، والتبديل بين الشبكة والقائمة بأريحية كاملة.</p>
            <p><a href='" . route('sales.my') . "'>اضغط هنا للذهاب لصفحة مبيعاتي لمشاهدة البيانات الغنية</a></p>";
});

