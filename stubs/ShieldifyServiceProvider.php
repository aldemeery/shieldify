<?php

namespace App\Providers;

use Aldemeery\Shieldify\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Aldemeery\Shieldify\Features;
use Aldemeery\Shieldify\Shieldify;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use App\Actions\Shieldify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use App\Actions\Shieldify\ResetUserPassword;
use App\Actions\Shieldify\UpdateUserPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Actions\Shieldify\UpdateUserProfileInformation;

class ShieldifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Shieldify::createUsersUsing(CreateNewUser::class);
        Shieldify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Shieldify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Shieldify::resetUserPasswordsUsing(ResetUserPassword::class);
        $this->registerEmailVerificationUrl();
        $this->registerPasswordResetUrl();

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email . $request->ip());
        });


        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by(Key::from($request->key)->id());
        });
    }

    /**
     * Register a callback that returns the email verification URL.
     *
     * @return void
     */
    private function registerEmailVerificationUrl(): void
    {
        if (Features::enabled(Features::emailVerification())) {
            VerifyEmail::createUrlUsing(function (MustVerifyEmail $notifiable) {
                $url = URL::temporarySignedRoute(
                    'verification.verify',
                    Carbon::now()->addMinutes(Config::get('shieldify.email_verification.expire', 60)),
                    [
                        'id' => $notifiable->getKey(),
                        'hash' => sha1($notifiable->getEmailForVerification()),
                    ],
                    false
                );

                $query = parse_url($url)['query'];

                return sprintf('%s?%s', rtrim(Config::get('shieldify.email_verification.url'), '/'), $query);
            });
        }
    }

    /**
     * Register a callback that returns the password reset URL.
     *
     * @return void
     */
    private function registerPasswordResetUrl(): void
    {
        if (Features::enabled(Features::resetPasswords())) {
            ResetPassword::createUrlUsing(function (CanResetPassword $notifiable, string $token) {
                return url(
                    sprintf(
                        '%s?token=%s&email=%s',
                        rtrim(Config::get('shieldify.password_reset.url'), '/'),
                        $token,
                        $notifiable->getEmailForPasswordReset()
                    )
                );
            });
        }
    }
}
