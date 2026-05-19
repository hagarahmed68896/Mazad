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
Route::get('/ads/{id}/bids/info', [BidController::class, 'info']);
Route::get('/ads/{id}/bids/history', [BidController::class, 'history']);
Route::post('/update-location', [LocationController::class, 'updateLocation']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/update-interests', [AuthController::class, 'updateInterests']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Ads creation
    Route::get('/ads/create/metadata', [\App\Http\Controllers\Api\AdController::class, 'createMetadata']);
    Route::get('/my-ads', [\App\Http\Controllers\Api\AdController::class, 'myAds']);
    Route::post('/ads', [\App\Http\Controllers\Api\AdController::class, 'store']);
    Route::post('/ads/{id}/update', [\App\Http\Controllers\Api\AdController::class, 'update']);
    Route::delete('/ads/{id}', [\App\Http\Controllers\Api\AdController::class, 'destroy']);
    Route::post('/ads/{id}/repost', [\App\Http\Controllers\Api\AdController::class, 'repost']);

    // Bidding
    Route::post('/ads/{id}/bids', [BidController::class, 'store']);

    // Orders (Buy Now)
    Route::post('/ads/{id}/orders', [OrderController::class, 'store']);

    // Wishlist
    Route::get('/wishlist', [\App\Http\Controllers\Api\WishlistController::class, 'index']);
    Route::post('/wishlist/toggle/{adId}', [\App\Http\Controllers\Api\WishlistController::class, 'toggle']);
    
    // Cart Routes
    Route::get('/cart', [\App\Http\Controllers\Api\CartController::class, 'index']);
    Route::post('/cart/add', [\App\Http\Controllers\Api\CartController::class, 'store']);
    Route::delete('/cart/remove/{id}', [\App\Http\Controllers\Api\CartController::class, 'destroy']);
    Route::post('/cart/clear', [\App\Http\Controllers\Api\CartController::class, 'clear']);

    // Chat Routes
    Route::get('/chats', [\App\Http\Controllers\Api\ChatController::class, 'index']);
    Route::get('/chats/{id}', [\App\Http\Controllers\Api\ChatController::class, 'show']);
    Route::post('/chats/{id}/send', [\App\Http\Controllers\Api\ChatController::class, 'sendMessage']);
    Route::delete('/chats/{id}', [\App\Http\Controllers\Api\ChatController::class, 'destroy']);
    Route::post('/users/{userId}/block', [\App\Http\Controllers\Api\ChatController::class, 'toggleBlock']);

    // Payment Route
    Route::post('/ads/{id}/payment', [\App\Http\Controllers\Api\AdController::class, 'processPayment']);

    // Question Routes
    Route::post('/ads/{id}/questions', [\App\Http\Controllers\Api\AdQuestionController::class, 'store']);
    Route::post('/questions/{id}/reply', [\App\Http\Controllers\Api\AdQuestionController::class, 'reply']);
    Route::post('/questions/{id}/toggle-visibility', [\App\Http\Controllers\Api\AdQuestionController::class, 'toggleVisibility']);

    // Review Routes
    Route::post('/reviews', [\App\Http\Controllers\Api\ReviewController::class, 'store']);
    Route::post('/reviews/{id}/reply', [\App\Http\Controllers\Api\ReviewController::class, 'reply']);
    Route::post('/reviews/{id}/toggle-visibility', [\App\Http\Controllers\Api\ReviewController::class, 'toggleVisibility']);
});
