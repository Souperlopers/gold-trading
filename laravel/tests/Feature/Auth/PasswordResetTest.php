<?php

namespace Tests\Feature\Auth;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/auth/reset-password';
    private string $validPhone = '09123456789';
    private string $validPassword = 'NewPassword123';
    private string $validNationalId = '1362964441';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('auth.otp.token_expiry', 600);
    }

    /**
     * Helper: Create a user with known credentials.
     */
    private function createUser(array $attributes = []): User
    {
        return User::create([
            'phone'       => $attributes['phone'] ?? $this->validPhone,
            'name'        => $attributes['name'] ?? 'John Doe',
            'password'    => Hash::make($attributes['password'] ?? 'OldPassword123'),
            'national_id' => $attributes['national_id'] ?? $this->validNationalId,
        ]);
    }

    /**
     * Helper: Create a verified OTP token for password reset.
     */
    private function createVerifiedOtpToken(?string $phone = null, bool $expired = false): string
    {
        $phone = $phone ?? $this->validPhone;
        $token = Str::random(40);
        $usedAt = $expired
            ? now()->subSeconds(Config::get('auth.otp.token_expiry') + 10)
            : now();

        $otp = OtpCode::create([
            'phone'              => $phone,
            'code'               => '123456',
            'purpose'            => 'password_reset',
            'verification_token' => hash('sha256', $token),
            'token_used_at'            => $usedAt,
        ]);
        $otp->created_at = $usedAt;
        $otp->save();

        return $token;
    }

    // ==================== SUCCESS TESTS ====================

    public function test_reset_password_successfully_without_logout_others()
    {
        $user = $this->createUser();
        $user->createToken('auth-token');
        $otpToken = $this->createVerifiedOtpToken($user->phone);

        $response = $this->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'logout_others'  => false,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => Lang::get('auth.password.reset'),
            ]);

        // Password should be updated
        $user->refresh();
        $this->assertTrue(Hash::check($this->validPassword, $user->password));

        // Token should not be revoked (logout_others = false)
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id'   => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_reset_password_successfully_with_logout_others()
    {
        $user = $this->createUser();
        $otpToken = $this->createVerifiedOtpToken($user->phone);

        // Create multiple tokens (simulate multiple devices)
        $token1 = $user->createToken('device-1')->plainTextToken;
        $token2 = $user->createToken('device-2')->plainTextToken;
        $token3 = $user->createToken('device-3')->plainTextToken;

        // Get the current token (the one we'll use for the request)
        $currentToken = $user->createToken('current-device')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $currentToken,
        ])->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'logout_others'  => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => Lang::get('auth.password.reset'),
            ]);

        // Password should be updated
        $user->refresh();
        $this->assertTrue(Hash::check($this->validPassword, $user->password));

        // Only the current token should remain (all others deleted)
        $currentTokenId = PersonalAccessToken::findToken($currentToken)?->id;
        $remainingTokens = $user->tokens()->get();

        $this->assertCount(1, $remainingTokens);
        $this->assertEquals($currentTokenId, $remainingTokens->first()->id);
    }

    public function test_reset_password_keeps_current_token_when_logout_others_true()
    {
        $user = $this->createUser();
        $otpToken = $this->createVerifiedOtpToken($user->phone);

        // Create a token and use it for authentication
        $currentToken = $user->createToken('current-device')->plainTextToken;

        // Create another token
        $user->createToken('other-device')->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $currentToken,
        ])->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'logout_others'  => true,
        ]);

        // Current token should still exist
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'id'           => PersonalAccessToken::findToken($currentToken)->id,
        ]);

        // Other token should be deleted
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_reset_password_keeps_all_tokens_when_logout_others_false()
    {
        $user = $this->createUser();
        $otpToken = $this->createVerifiedOtpToken($user->phone);

        // Create multiple tokens
        $user->createToken('device-1')->plainTextToken;
        $user->createToken('device-2')->plainTextToken;
        $currentToken = $user->createToken('current-device')->plainTextToken;

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $currentToken,
        ])->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'logout_others'  => false,
        ]);

        // All tokens should still exist (3 total)
        $this->assertDatabaseCount('personal_access_tokens', 3);
    }

    // ==================== FAILURE TESTS ====================

    public function test_reset_fails_with_invalid_otp_token()
    {
        $user = $this->createUser();

        $response = $this->postJson($this->endpoint, [
            'otp_token'      => '0123456789012345678901234567890123456789',
            'password'       => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'logout_others'  => false,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => Lang::get('auth.password.token'),
            ]);

        // Password should NOT be updated
        $user->refresh();
        $this->assertFalse(Hash::check($this->validPassword, $user->password));
    }

    public function test_reset_fails_with_expired_otp_token()
    {
        $user = $this->createUser();
        $otpToken = $this->createVerifiedOtpToken($user->phone, expired: true);

        $response = $this->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'logout_others'  => false,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => Lang::get('auth.password.token'),
            ]);

        // Password should NOT be updated
        $user->refresh();
        $this->assertFalse(Hash::check($this->validPassword, $user->password));
    }

    public function test_reset_fails_when_otp_token_missing()
    {
        $this->createUser();

        $response = $this->postJson($this->endpoint, [
            'password'       => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'logout_others'  => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['otp_token']);
    }

    public function test_reset_fails_when_password_missing()
    {
        $user = $this->createUser();
        $otpToken = $this->createVerifiedOtpToken($user->phone);

        $response = $this->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password_confirmation' => $this->validPassword,
            'logout_others'  => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_fails_when_password_confirmation_does_not_match()
    {
        $user = $this->createUser();
        $otpToken = $this->createVerifiedOtpToken($user->phone);

        $response = $this->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => $this->validPassword,
            'password_confirmation' => 'DifferentPassword123',
            'logout_others'  => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_fails_when_password_is_too_short()
    {
        $user = $this->createUser();
        $otpToken = $this->createVerifiedOtpToken($user->phone);

        $response = $this->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => 'Pass1',
            'password_confirmation' => 'Pass1',
            'logout_others'  => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_fails_when_password_has_no_uppercase()
    {
        $user = $this->createUser();
        $otpToken = $this->createVerifiedOtpToken($user->phone);

        $response = $this->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => 'password123',
            'password_confirmation' => 'password123',
            'logout_others'  => false,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_reset_fails_when_user_not_found_for_phone()
    {
        // No user created for this phone
        $otpToken = $this->createVerifiedOtpToken($this->validPhone);

        $response = $this->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'logout_others'  => false,
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => Lang::get('auth.password.user'),
            ]);
    }

    public function test_reset_fails_when_logout_others_is_not_boolean()
    {
        $user = $this->createUser();
        $otpToken = $this->createVerifiedOtpToken($user->phone);

        $response = $this->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'logout_others'  => 'not_a_boolean',
        ]);

        // The validation rule likely expects boolean
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['logout_others']);
    }

    // ==================== INTEGRATION TESTS ====================

    public function test_reset_password_and_login_with_new_password()
    {
        $user = $this->createUser();
        $oldPassword = 'OldPassword123';
        $newPassword = 'NewPassword456';

        // Ensure old password works
        $loginResponse = $this->postJson('/api/auth/login', [
            'phone'    => $user->phone,
            'password' => $oldPassword,
            'client'   => 'mobile',
        ]);
        $loginResponse->assertStatus(200);

        // Reset password
        $otpToken = $this->createVerifiedOtpToken($user->phone);
        $resetResponse = $this->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => $newPassword,
            'password_confirmation' => $newPassword,
            'logout_others'  => false,
        ]);
        $resetResponse->assertStatus(200);

        // Old password should fail
        $loginResponse = $this->postJson('/api/auth/login', [
            'phone'    => $user->phone,
            'password' => $oldPassword,
            'client'   => 'mobile',
        ]);
        $loginResponse->assertStatus(401);

        // New password should work
        $loginResponse = $this->postJson('/api/auth/login', [
            'phone'    => $user->phone,
            'password' => $newPassword,
            'client'   => 'mobile',
        ]);
        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    public function test_reset_password_preserves_other_user_data()
    {
        $user = $this->createUser([
            'name' => 'Original Name',
            'national_id' => '1234567890',
        ]);

        $otpToken = $this->createVerifiedOtpToken($user->phone);

        $this->postJson($this->endpoint, [
            'otp_token'      => $otpToken,
            'password'       => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'logout_others'  => false,
        ]);

        $user->refresh();

        // Only password should change
        $this->assertEquals('Original Name', $user->name);
        $this->assertEquals('1234567890', $user->national_id);
        $this->assertTrue(Hash::check($this->validPassword, $user->password));
    }
}
