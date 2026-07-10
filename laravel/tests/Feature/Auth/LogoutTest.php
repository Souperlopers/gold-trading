<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/auth/logout';
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

    public function test_user_can_logout()
    {
        $user = $this->createUser();

        // Log in via mobile to get a token
        $loginResponse = $this->postJson('/api/auth/login', [
            'phone'    => $this->validPhone,
            'password' => $this->validPassword,
            'client'   => 'mobile',
        ]);

        $token = $loginResponse->json('token');

        // Use the token to logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson($this->endpoint, ['client' => 'mobile']);

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'message' => Lang::get('auth.logout.success'),
            ]);

        // Assert token is revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id'   => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_user_cant_logout_with_wrong_token()
    {
        $user = $this->createUser();

        // Log in via mobile to get a token
        $loginResponse = $this->postJson('/api/auth/login', [
            'phone'    => $this->validPhone,
            'password' => $this->validPassword,
            'client'   => 'mobile',
        ]);

        $wrongToken = "wrongToekn";

        // Use the token to logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $wrongToken,
        ])->postJson($this->endpoint, ['client' => 'mobile']);

        $logoutResponse->assertStatus(401);
    }

    public function test_logout_fails_without_authentication()
    {
        $response = $this->postJson($this->endpoint);

        $response->assertStatus(401);
    }
}
