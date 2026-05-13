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
});
