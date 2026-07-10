<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/auth/login';
    private string $validPhone = '09123456789';
    private string $validPassword = 'Password123';
    private string $validName = 'John Doe';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('sanctum.expiration', null);
    }

    /**
     * Helper: Create a user with known credentials.
     */
    private function createUser(array $attributes = []): User
    {
        return User::create([
            'phone'       => $attributes['phone'] ?? $this->validPhone,
            'name'        => $attributes['name'] ?? $this->validName,
            'password'    => Hash::make($attributes['password'] ?? $this->validPassword),
            'national_id' => $attributes['national_id'] ?? '1234567890',
        ]);
    }

    // ==================== SUCCESSFUL LOGIN ====================

    public function test_user_can_login_with_mobile_client()
    {
        $this->createUser();

        $response = $this->postJson($this->endpoint, [
            'phone'    => $this->validPhone,
            'password' => $this->validPassword,
            'client'   => 'mobile',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['name', 'phone', 'national_id'],
            ])
            ->assertJson([
                'message' => Lang::get('auth.login.success'),
            ]);

        $this->assertAuthenticatedAs(User::first(), 'sanctum');
    }

    // public function test_user_can_login_with_web_client()
    // {
    //     $this->createUser();

    //     // ✅ Start session for web authentication
    //     $this->app['session']->start();

    //     $response = $this->withSession([])->postJson($this->endpoint, [
    //         'phone'    => $this->validPhone,
    //         'password' => $this->validPassword,
    //         'client'   => 'web',
    //     ]);

    //     $response->assertStatus(200)
    //         ->assertJson([
    //             'message' => Lang::get('auth.login.success'),
    //             'user'    => ['phone' => $this->validPhone],
    //         ]);

    //     $this->assertAuthenticated('web');
    // }

    // ==================== FAILED LOGIN ====================

    public function test_login_fails_with_invalid_phone()
    {
        // No user created

        $response = $this->postJson($this->endpoint, [
            'phone'    => $this->validPhone,
            'password' => $this->validPassword,
            'client'   => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => Lang::get('validation.exists', ['attribute' => 'phone number']),
            ]);
    }

    public function test_login_fails_with_wrong_password()
    {
        $this->createUser();

        $response = $this->postJson($this->endpoint, [
            'phone'    => $this->validPhone,
            'password' => 'wrongpassword',
            'client'   => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'errors' => ['password' => [Lang::get('validation.password.mixed', ['attribute' => 'password'])]],
            ]);
    }

    public function test_login_fails_when_phone_missing()
    {
        $response = $this->postJson($this->endpoint, [
            'password' => $this->validPassword,
            'client'   => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_login_fails_when_password_missing()
    {
        $response = $this->postJson($this->endpoint, [
            'phone'  => $this->validPhone,
            'client' => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_fails_when_phone_format_invalid()
    {
        $response = $this->postJson($this->endpoint, [
            'phone'    => '1234567890', // doesn't start with 09
            'password' => $this->validPassword,
            'client'   => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_login_fails_when_client_invalid()
    {
        $this->createUser();

        $response = $this->postJson($this->endpoint, [
            'phone'    => $this->validPhone,
            'password' => $this->validPassword,
            'client'   => 'invalid_client',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client']);
    }

    public function test_login_fails_when_client_missing()
    {
        $this->createUser();

        $response = $this->postJson($this->endpoint, [
            'phone'    => $this->validPhone,
            'password' => $this->validPassword,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client']);
    }
}
