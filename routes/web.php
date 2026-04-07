<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('post-login');

    Route::get('verify-otp', [AuthController::class, 'showVerifyOtpForm'])->name('verify-otp');
    Route::post('verify-otp', [AuthController::class, 'verifyOtp'])->name('post-verify');

    Route::get('forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('forgot-password');
    Route::post('forgot-password', [AuthController::class, 'sendResetLink'])->name('post-forgot-password');

    Route::get('reset-password', [AuthController::class, 'showResetPasswordForm'])->name('reset-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('post-reset-password');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('verification-success', [AuthController::class, 'showVerificationSuccess'])->name('verification.success');
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

        // Accounts Management
        Route::group(['prefix' => 'accounts', 'as' => 'accounts.'], function () {
            Route::get('individuals', [\App\Http\Controllers\Admin\UserController::class, 'individuals'])->name('individuals');
            Route::get('individuals/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('individuals.create');
            Route::post('individuals/bulk-destroy', [\App\Http\Controllers\Admin\UserController::class, 'bulkDestroy'])->name('individuals.bulk-destroy');
            Route::get('individuals/download', [\App\Http\Controllers\Admin\UserController::class, 'download'])->name('individuals.download');
            Route::get('individuals/{id}', [\App\Http\Controllers\Admin\UserController::class, 'show'])->name('individuals.show');
            Route::get('individuals/{id}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('individuals.edit');
            Route::delete('individuals/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('individuals.destroy');
        });
    });
});
