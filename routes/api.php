<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\BidController;
use App\Http\Controllers\Api\OrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/home', [HomeController::class, 'index']);
Route::get('/categories', [HomeController::class, 'categories']);
Route::get('/categories/{id}/properties', [SearchController::class, 'properties']);
Route::get('/cities', [LocationController::class, 'allCities']);
Route::get('/search', [SearchController::class, 'index']);
Route::get('/search/filters', [SearchController::class, 'filters']);
Route::get('/regions', [LocationController::class, 'regions']);
Route::get('/regions/{regionId}/cities', [LocationController::class, 'cities']);
Route::get('/ads/{id}', [\App\Http\Controllers\Api\AdController::class, 'show']);
Route::get('/ads/{id}/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'adReviews']);
Route::get('/services/{id}', [HomeController::class, 'serviceShow']);
Route::get('/ads/{id}/bids/info', [BidController::class, 'info']);
Route::get('/ads/{id}/bids/history', [BidController::class, 'history']);
Route::post('/update-location', [LocationController::class, 'updateLocation']);
Route::get('/users/{id}/profile', [HomeController::class, 'userProfile']);
Route::get('/users/{id}/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'userReviews']);

// Support and Help Desk Pages (Public)
Route::get('/faqs', [\App\Http\Controllers\Api\SupportController::class, 'faqs']);
Route::get('/privacy-policy', [\App\Http\Controllers\Api\SupportController::class, 'privacy']);
Route::get('/terms-conditions', [\App\Http\Controllers\Api\SupportController::class, 'terms']);
Route::get('/contact-info', [\App\Http\Controllers\Api\SupportController::class, 'contactInfo']);
Route::post('/contact-us', [\App\Http\Controllers\Api\SupportController::class, 'storeTicket']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/profile/password', [AuthController::class, 'updatePassword']);
    Route::post('/profile/bank', [AuthController::class, 'updateBank']);
    Route::post('/profile/avatar', [AuthController::class, 'updateAvatar']);
    Route::post('/profile/cover', [AuthController::class, 'updateCover']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/update-interests', [AuthController::class, 'updateInterests']);
    Route::get('/interests', [AuthController::class, 'getInterests']);
    Route::get('/profile/notifications', [AuthController::class, 'getNotificationSettings']);
    Route::post('/profile/notifications', [AuthController::class, 'updateNotificationSettings']);
    Route::post('/profile/fcm-token', [AuthController::class, 'updateFcmToken']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::match(['delete', 'post'], '/notifications/{id}', [NotificationController::class, 'destroy']);
    Route::post('/notifications/{id}/delete', [NotificationController::class, 'destroy']);

    // Ads creation
    Route::get('/ads/create/metadata', [\App\Http\Controllers\Api\AdController::class, 'createMetadata']);
    Route::get('/my-ads', [\App\Http\Controllers\Api\AdController::class, 'myAds']);
    Route::post('/ads', [\App\Http\Controllers\Api\AdController::class, 'store']);
    Route::post('/ads/{id}/update', [\App\Http\Controllers\Api\AdController::class, 'update']);
    Route::delete('/ads/{id}', [\App\Http\Controllers\Api\AdController::class, 'destroy']);
    Route::post('/ads/{id}/delete', [\App\Http\Controllers\Api\AdController::class, 'destroy']);
    Route::post('/ads/{id}/repost', [\App\Http\Controllers\Api\AdController::class, 'repost']);
    Route::post('/ads/repost/bulk', [\App\Http\Controllers\Api\AdController::class, 'repostBulk']);

    // Bidding
    Route::post('/ads/{id}/bids', [BidController::class, 'store']);

    // Orders (Buy Now) & Sales Management
    Route::post('/ads/{id}/orders', [OrderController::class, 'store']);
    Route::get('/my-sales', [OrderController::class, 'mySales']);
    Route::get('/my-purchases', [OrderController::class, 'myPurchases']);
    Route::post('/my-sales/seed', [OrderController::class, 'seedMySales']);
    Route::post('/my-sales/bulk-mark-sold', [OrderController::class, 'bulkMarkSold']);
    Route::post('/orders/{id}/confirm-delivery', [OrderController::class, 'confirmDelivery']);

    // Reports & Analytics
    Route::get('/reports', [\App\Http\Controllers\Api\ReportController::class, 'index']);

    // Wishlist
    Route::get('/wishlist', [\App\Http\Controllers\Api\WishlistController::class, 'index']);
    Route::post('/wishlist/toggle/{adId}', [\App\Http\Controllers\Api\WishlistController::class, 'toggle']);

    // Look Later
    Route::get('/look-later', [\App\Http\Controllers\Api\LookLaterController::class, 'index']);
    Route::post('/look-later/toggle/{adId}', [\App\Http\Controllers\Api\LookLaterController::class, 'toggle']);

    // Bids & Offers
    Route::get('/bids-offers', [\App\Http\Controllers\Api\BidController::class, 'myBidsOffers']);
    Route::post('/bids-offers/{bidId}/accept', [\App\Http\Controllers\Api\BidController::class, 'acceptBidOffer']);
    Route::post('/bids-offers/{bidId}/reject', [\App\Http\Controllers\Api\BidController::class, 'rejectBidOffer']);
    Route::post('/bids-offers/{bidId}/counter', [\App\Http\Controllers\Api\BidController::class, 'counterBidOffer']);
    
    // Cart Routes
    Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'index']);
    Route::post('/cart/add', [\App\Http\Controllers\Api\CartController::class, 'store']);
    Route::patch('/cart/update/{id}', [\App\Http\Controllers\Api\CartController::class, 'update']);
    Route::match(['delete', 'post'], '/cart/remove/{id}', [\App\Http\Controllers\Api\CartController::class, 'destroy']);
    Route::post('/cart/clear', [\App\Http\Controllers\Api\CartController::class, 'clear']);
    Route::post('/cart/checkout', [\App\Http\Controllers\Api\CartController::class, 'checkout']);

    // Chat Routes
    Route::get('/chats', [\App\Http\Controllers\Api\ChatController::class, 'index']);
    Route::post('/chats/start', [\App\Http\Controllers\Api\ChatController::class, 'startConversation']);
    Route::get('/chats/{id}', [\App\Http\Controllers\Api\ChatController::class, 'show']);
    Route::post('/chats/{id}/send', [\App\Http\Controllers\Api\ChatController::class, 'sendMessage']);
    Route::delete('/chats/{id}', [\App\Http\Controllers\Api\ChatController::class, 'destroy']);
    Route::post('/users/{userId}/block', [\App\Http\Controllers\Api\ChatController::class, 'toggleBlock']);
    Route::post('/users/{id}/report', [\App\Http\Controllers\Api\HomeController::class, 'reportUser']);

    // Payment & Promotion Routes
    Route::post('/ads/{id}/payment', [\App\Http\Controllers\Api\AdController::class, 'processPayment']);
    Route::post('/ads/promote/bulk', [\App\Http\Controllers\Api\AdController::class, 'processBulkPromotion']);

    // Question Routes
    Route::post('/ads/{id}/questions', [\App\Http\Controllers\Api\AdQuestionController::class, 'store']);
    Route::post('/questions/{id}/reply', [\App\Http\Controllers\Api\AdQuestionController::class, 'reply']);
    Route::post('/questions/{id}/toggle-visibility', [\App\Http\Controllers\Api\AdQuestionController::class, 'toggleVisibility']);

    // Review Routes
    Route::post('/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']);
    Route::post('/reviews/{id}/reply', [\App\Http\Controllers\Api\ReviewController::class, 'reply']);
    Route::post('/reviews/{id}/toggle-visibility', [\App\Http\Controllers\Api\ReviewController::class, 'toggleVisibility']);
    Route::get('/my-reviews', [\App\Http\Controllers\Api\ReviewController::class, 'myReviews']);
    // Inquiries Route
    Route::get('/my-inquiries', [\App\Http\Controllers\Api\SupportController::class, 'myInquiries']);
    Route::post('/search/history/clear', [SearchController::class, 'clearHistory']);

    // Team Management Routes
    Route::get('/settings/team', [\App\Http\Controllers\Api\TeamController::class, 'index']);
    Route::post('/settings/team', [\App\Http\Controllers\Api\TeamController::class, 'store']);
    Route::get('/settings/team/{id}', [\App\Http\Controllers\Api\TeamController::class, 'show']);
    Route::post('/settings/team/{id}/update', [\App\Http\Controllers\Api\TeamController::class, 'update']);
    Route::delete('/settings/team/{id}', [\App\Http\Controllers\Api\TeamController::class, 'destroy']);

    // Broadcasting authentication
    \Illuminate\Support\Facades\Broadcast::routes(['middleware' => ['auth:sanctum']]);
});

