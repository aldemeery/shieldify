<?php

namespace Aldemeery\Shieldify;

// phpcs:disable

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Aldemeery\Shieldify\Http\Responses\LoginResponse;
use Aldemeery\Shieldify\Http\Responses\LogoutResponse;
use Aldemeery\Shieldify\Http\Responses\LockoutResponse;
use Aldemeery\Shieldify\Http\Responses\RegisterResponse;
use Aldemeery\Shieldify\Http\Responses\PasswordResetResponse;
use Aldemeery\Shieldify\Http\Responses\PasswordUpdateResponse;
use Aldemeery\Shieldify\Http\Responses\TwoFactorLoginResponse;
use Aldemeery\Shieldify\Http\Responses\PasswordConfirmedResponse;
use Aldemeery\Shieldify\Http\Responses\FailedPasswordResetResponse;
use Aldemeery\Shieldify\Http\Responses\FailedTwoFactorLoginResponse;
use Aldemeery\Shieldify\Contracts\LoginResponse as LoginResponseContract;
use Aldemeery\Shieldify\Http\Responses\FailedPasswordConfirmationResponse;
use Aldemeery\Shieldify\Contracts\LogoutResponse as LogoutResponseContract;
use Aldemeery\Shieldify\Contracts\LockoutResponse as LockoutResponseContract;
use Aldemeery\Shieldify\Http\Responses\FailedPasswordResetLinkRequestResponse;
use Aldemeery\Shieldify\Contracts\RegisterResponse as RegisterResponseContract;
use Aldemeery\Shieldify\Http\Responses\SuccessfulPasswordResetLinkRequestResponse;
use Aldemeery\Shieldify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Aldemeery\Shieldify\Contracts\PasswordUpdateResponse as PasswordUpdateResponseContract;
use Aldemeery\Shieldify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Aldemeery\Shieldify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;
use Aldemeery\Shieldify\Contracts\FailedPasswordResetResponse as FailedPasswordResetResponseContract;
use Aldemeery\Shieldify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;
use Aldemeery\Shieldify\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Aldemeery\Shieldify\Contracts\FailedPasswordConfirmationResponse as FailedPasswordConfirmationResponseContract;
use Aldemeery\Shieldify\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;
use Aldemeery\Shieldify\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;

class ShieldifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/shieldify.php', 'shieldify');

        $this->registerResponseBindings();

        $this->app->singleton(
            TwoFactorAuthenticationProviderContract::class,
            TwoFactorAuthenticationProvider::class
        );

        $this->app->bind(Guard::class, function () {
            $guard = Auth::guard(config('shieldify.guard', null));

            if (is_null($guard->getProvider())) {
                $guard->setProvider(Auth::createUserProvider(config('auth.defaults.provider', 'users')));
            }

            return $guard;
        });
    }

    /**
     * Register the response bindings.
     *
     * @return void
     */
    protected function registerResponseBindings()
    {
        $this->app->singleton(FailedPasswordConfirmationResponseContract::class, FailedPasswordConfirmationResponse::class);
        $this->app->singleton(FailedPasswordResetLinkRequestResponseContract::class, FailedPasswordResetLinkRequestResponse::class);
        $this->app->singleton(FailedPasswordResetResponseContract::class, FailedPasswordResetResponse::class);
        $this->app->singleton(FailedTwoFactorLoginResponseContract::class, FailedTwoFactorLoginResponse::class);
        $this->app->singleton(LockoutResponseContract::class, LockoutResponse::class);
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(TwoFactorLoginResponseContract::class, TwoFactorLoginResponse::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
        $this->app->singleton(PasswordConfirmedResponseContract::class, PasswordConfirmedResponse::class);
        $this->app->singleton(PasswordResetResponseContract::class, PasswordResetResponse::class);
        $this->app->singleton(PasswordUpdateResponseContract::class, PasswordUpdateResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
        $this->app->singleton(SuccessfulPasswordResetLinkRequestResponseContract::class, SuccessfulPasswordResetLinkRequestResponse::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configurePublishing();
        $this->configureRoutes();
    }

    /**
     * Configure the publishable resources offered by the package.
     *
     * @return void
     */
    protected function configurePublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../stubs/shieldify.php' => config_path('shieldify.php'),
            ], 'shieldify-config');

            $this->publishes([
                __DIR__ . '/../stubs/CreateNewUser.php' => app_path('Actions/Shieldify/CreateNewUser.php'),
                __DIR__ . '/../stubs/ShieldifyServiceProvider.php' => app_path('Providers/ShieldifyServiceProvider.php'),
                __DIR__ . '/../stubs/PasswordValidationRules.php' => app_path('Actions/Shieldify/PasswordValidationRules.php'),
                __DIR__ . '/../stubs/ResetUserPassword.php' => app_path('Actions/Shieldify/ResetUserPassword.php'),
                __DIR__ . '/../stubs/UpdateUserProfileInformation.php' => app_path('Actions/Shieldify/UpdateUserProfileInformation.php'),
                __DIR__ . '/../stubs/UpdateUserPassword.php' => app_path('Actions/Shieldify/UpdateUserPassword.php'),
            ], 'shieldify-support');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'shieldify-migrations');
        }
    }

    /**
     * Configure the routes offered by the application.
     *
     * @return void
     */
    protected function configureRoutes()
    {
        if (Shieldify::$registersRoutes) {
            Route::group([
                'namespace' => 'Aldemeery\Shieldify\Http\Controllers',
                'domain' => config('shieldify.domain', null),
                'prefix' => config('shieldify.prefix'),
            ], function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
            });
        }
    }
}

// phpcs:enable
