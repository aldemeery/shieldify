<?php

namespace Aldemeery\Shieldify;

use Aldemeery\Shieldify\Features;
use Laravel\Sanctum\HasApiTokens;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Event;
use Aldemeery\Shieldify\LoginRateLimiter;
use Laravel\Sanctum\SanctumServiceProvider;
use Illuminate\Testing\Fluent\AssertableJson;
use Aldemeery\Shieldify\Tests\OrchestraTestCase;
use Aldemeery\Shieldify\ShieldifyServiceProvider;
use Aldemeery\Shieldify\TwoFactorAuthenticatable;
use Aldemeery\Shieldify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Sanctum\Contracts\HasApiTokens as HasApiTokensContract;

class AuthenticationControllerTest extends OrchestraTestCase
{
    public function test_user_can_authenticate()
    {
        TestAuthenticationUser::forceCreate([
            'name' => 'Osama Aldemeery',
            'email' => 'aldemeery@gmail.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withoutExceptionHandling()->postJson('/login', [
            'email' => 'aldemeery@gmail.com',
            'password' => 'secret',
        ]);

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) {
            $json->hasAll('token', 'two_factor')
                ->where('two_factor', false);
        });
    }

    public function test_user_is_redirected_to_challenge_when_using_two_factor_authentication()
    {
        Event::fake();

        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationUser::class);

        TestTwoFactorAuthenticationUser::forceCreate([
            'name' => 'Osama Aldemeery',
            'email' => 'aldemeery@gmail.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => 'test-secret',
        ]);

        $response = $this->withoutExceptionHandling()->postJson('/login', [
            'email' => 'aldemeery@gmail.com',
            'password' => 'secret',
        ]);

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) {
            $json->hasAll('key', 'two_factor')
                ->where('two_factor', true);
        });

        Event::assertDispatched(TwoFactorAuthenticationChallenged::class);
    }

    public function test_user_can_authenticate_when_two_factor_challenge_is_disabled()
    {
        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationUser::class);

        $features = app('config')->get('shieldify.features');

        unset($features[array_search(Features::twoFactorAuthentication(), $features)]);

        app('config')->set('shieldify.features', $features);

        TestTwoFactorAuthenticationUser::forceCreate([
            'name' => 'Osama Aldemeery',
            'email' => 'aldemeery@gmail.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => 'test-secret',
        ]);

        $response = $this->withoutExceptionHandling()->postJson('/login', [
            'email' => 'aldemeery@gmail.com',
            'password' => 'secret',
        ]);

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) {
            $json->hasAll('token', 'two_factor')
                ->where('two_factor', false);
        });
    }

    public function test_validation_exception_returned_on_failure()
    {
        TestAuthenticationUser::forceCreate([
            'name' => 'Osama Aldemeery',
            'email' => 'aldemeery@gmail.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->postJson('/login', [
            'email' => 'aldemeery@gmail.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_login_attempts_are_throttled()
    {
        $this->mock(LoginRateLimiter::class, function ($mock) {
            $mock->shouldReceive('tooManyAttempts')->andReturn(true);
            $mock->shouldReceive('availableIn')->andReturn(10);
        });

        $response = $this->postJson('/login', [
            'email' => 'aldemeery@gmail.com',
            'password' => 'secret',
        ]);

        $response->assertStatus(429);
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_the_user_can_logout_of_the_application()
    {
        $user = TestAuthenticationUser::forceCreate([
            'name' => 'Osama Aldemeery',
            'email' => 'aldemeery@gmail.com',
            'password' => bcrypt('secret'),
        ]);

        $response = $this->withToken($user->createToken('Test')->plainTextToken)->postJson('/logout');

        $response->assertStatus(204);
        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_two_factor_challenge_can_be_passed_via_code()
    {
        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationUser::class);

        $tfaEngine = app(Google2FA::class);
        $userSecret = $tfaEngine->generateSecretKey();
        $validOtp = $tfaEngine->getCurrentOtp($userSecret);

        $user = TestTwoFactorAuthenticationUser::forceCreate([
            'name' => 'Osama Aldemeery',
            'email' => 'aldemeery@gmail.com',
            'password' => bcrypt('secret'),
            'two_factor_secret' => encrypt($userSecret),
        ]);

        $response = $this->withoutExceptionHandling()
            ->post('/two-factor-challenge', [
                'key' => (string) (new Key($user->id, time() + 60)),
                'code' => $validOtp,
            ]);

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) {
            $json->has('token');
        });
    }

    public function test_two_factor_challenge_can_be_passed_via_recovery_code()
    {
        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationUser::class);

        $user = TestTwoFactorAuthenticationUser::forceCreate([
            'name' => 'Osama Aldemeery',
            'email' => 'aldemeery@gmail.com',
            'password' => bcrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['invalid-code', 'valid-code'])),
        ]);

        $response = $this->withoutExceptionHandling()
            ->postJson('/two-factor-challenge', [
                'key' => (string) (new Key($user->id, time() + 60)),
                'recovery_code' => 'valid-code',
            ]);

        $response->assertOk();
        $response->assertJson(function (AssertableJson $json) {
            $json->has('token');
        });
        $this->assertNotContains('valid-code', json_decode(decrypt($user->fresh()->two_factor_recovery_codes), true));
    }

    public function test_two_factor_challenge_can_fail_via_recovery_code()
    {
        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationUser::class);

        $user = TestTwoFactorAuthenticationUser::forceCreate([
            'name' => 'Osama Aldemeery',
            'email' => 'aldemeery@gmail.com',
            'password' => bcrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['invalid-code', 'valid-code'])),
        ]);

        $response = $this->postJson('/two-factor-challenge', [
            'key' => (string) (new Key($user->id, time() + 60)),
            'recovery_code' => 'missing-code',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['code']);
    }

    public function test_two_factor_challenge_requires_a_key()
    {
        app('config')->set('auth.providers.users.model', TestTwoFactorAuthenticationUser::class);

        $response = $this->postJson('/two-factor-challenge');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['key']);
    }

    protected function getPackageProviders($app)
    {
        return [
            SanctumServiceProvider::class,
            ShieldifyServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadLaravelMigrations(['--database' => 'testbench']);

        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback', ['--database' => 'testbench'])->run();
        });
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['migrator']->path(__DIR__.'/../database/migrations');

        $app['config']->set('auth.providers.users.model', TestAuthenticationUser::class);

        $app['config']->set('database.default', 'testbench');

        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}

class TestAuthenticationUser extends User implements HasApiTokensContract
{
    use HasApiTokens;

    protected $table = 'users';
}

class TestTwoFactorAuthenticationUser extends User implements HasApiTokensContract
{
    use HasApiTokens;
    use TwoFactorAuthenticatable;

    protected $table = 'users';
}
