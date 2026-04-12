<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');

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
    });
});
