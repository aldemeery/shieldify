<?php

use Aldemeery\Shieldify\Features;
use Illuminate\Support\Facades\Route;
use Aldemeery\Shieldify\Http\Middleware\RequirePassword;
use Aldemeery\Shieldify\Http\Controllers\PasswordController;
use Aldemeery\Shieldify\Http\Controllers\NewPasswordController;
use Aldemeery\Shieldify\Http\Controllers\VerifyEmailController;
use Aldemeery\Shieldify\Http\Controllers\RecoveryCodeController;
use Aldemeery\Shieldify\Http\Controllers\AuthenticationController;
use Aldemeery\Shieldify\Http\Controllers\RegisteredUserController;
use Aldemeery\Shieldify\Http\Controllers\TwoFactorQrCodeController;
use Aldemeery\Shieldify\Http\Controllers\PasswordResetLinkController;
use Aldemeery\Shieldify\Http\Controllers\ProfileInformationController;
use Aldemeery\Shieldify\Http\Controllers\ConfirmablePasswordController;
use Aldemeery\Shieldify\Http\Controllers\ConfirmedPasswordStatusController;
use Aldemeery\Shieldify\Http\Controllers\TwoFactorAuthenticationController;
use Aldemeery\Shieldify\Http\Controllers\EmailVerificationNotificationController;

Route::group(['middleware' => config('shieldify.middleware', ['api'])], function () {
    $limiter = config('shieldify.limiters.login');
    $twoFactorLimiter = config('shieldify.limiters.two-factor');
    $verificationLimiter = config('shieldify.limiters.verification', '6,1');

    // Authentication...
    Route::post('/login', [AuthenticationController::class, 'store'])
        ->middleware(array_filter([
            'guest:' . config('shieldify.guard'),
            $limiter ? 'throttle:' . $limiter : null,
        ]));

    Route::post('/logout', [AuthenticationController::class, 'destroy'])
        ->middleware('auth:' . config('shieldify.guard'))
        ->name('logout');

    // Password Reset...
    if (Features::enabled(Features::resetPasswords())) {
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware(['guest:' . config('shieldify.guard')])
            ->name('password.email');

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->middleware(['guest:' . config('shieldify.guard')])
            ->name('password.update');
    }

    // Registration...
    if (Features::enabled(Features::registration())) {
        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->middleware(['guest:' . config('shieldify.guard')]);
    }

    // Email Verification...
    if (Features::enabled(Features::emailVerification())) {
        Route::get('/email/verify', [VerifyEmailController::class, '__invoke'])
            ->middleware([
                config('shieldify.auth_middleware', 'auth') . ':' . config('shieldify.guard'),
                'signed:relative',
                'throttle:' . $verificationLimiter,
            ])
            ->name('verification.verify');

        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware([
                config('shieldify.auth_middleware', 'auth') . ':' . config('shieldify.guard'),
                'throttle:' . $verificationLimiter,
            ])
            ->name('verification.send');
    }

    // Profile Information...
    if (Features::enabled(Features::updateProfileInformation())) {
        Route::put('/user/profile-information', [ProfileInformationController::class, 'update'])
            ->middleware([config('shieldify.auth_middleware', 'auth') . ':' . config('shieldify.guard')])
            ->name('user-profile-information.update');
    }

    // Passwords...
    if (Features::enabled(Features::updatePasswords())) {
        Route::put('/user/password', [PasswordController::class, 'update'])
            ->middleware([config('shieldify.auth_middleware', 'auth') . ':' . config('shieldify.guard')])
            ->name('user-password.update');
    }

    // Password Confirmation...
    Route::get('/user/confirmed-password-status', [ConfirmedPasswordStatusController::class, 'show'])
        ->middleware([config('shieldify.auth_middleware', 'auth') . ':' . config('shieldify.guard')]);

    Route::post('/user/confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware([config('shieldify.auth_middleware', 'auth') . ':' . config('shieldify.guard')]);


    // Two Factor Authentication...
    if (Features::enabled(Features::twoFactorAuthentication())) {
        Route::post('/two-factor-challenge', [TwoFactorAuthenticationController::class, 'store'])
            ->middleware(array_filter([
                'guest:' . config('shieldify.guard'),
                $twoFactorLimiter ? 'throttle:' . $twoFactorLimiter : null,
            ]));

        $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? [config('shieldify.auth_middleware', 'auth') . ':' . config('shieldify.guard'), RequirePassword::class]
            : [config('shieldify.auth_middleware', 'auth') . ':' . config('shieldify.guard')];

        Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'enable'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.enable');

        Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'disable'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.disable');

        Route::get('/user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.qr-code');

        Route::get('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.recovery-codes');

        Route::post('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
            ->middleware($twoFactorMiddleware);
    }
});
