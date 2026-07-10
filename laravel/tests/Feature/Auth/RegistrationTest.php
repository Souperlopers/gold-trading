<?php

namespace Tests\Feature\Auth;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint        = '/api/auth/register';
    private string $validPhone      = '09309506250';
    private string $validName       = 'John Doe';
    private string $validPassword   = 'Password123';
    private string $validNationalId = '1362964441';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('auth.otp.token_expiry', 600); // 10 minutes
        Config::set('auth.otp.expiry', 300);
        Config::set('auth.otp.resend_wait', 60);
    }

    /**
     * Helper: Create a verified OTP token for a given phone.
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
            'purpose'            => 'registration',
            'verification_token' => hash('sha256', $token),
            'used_at'            => $usedAt,
        ]);
        $otp->created_at = $usedAt; // created_at is also set to match used_at for consistency
        $otp->save();

        return $token;
    }

    // ==================== SUCCESS TESTS ====================

    public function test_user_can_register_successfully_with_mobile_client()
    {
        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $this->validNationalId,
            'otp_token'             => $this->createVerifiedOtpToken(),
            'client'                => 'mobile',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['name', 'phone', 'national_id'],
            ]);

        $this->assertDatabaseHas('users', [
            'phone'       => $this->validPhone,
            'name'        => $this->validName,
            'national_id' => $this->validNationalId,
        ]);

        $user = User::first();
        $this->assertTrue(Hash::check($this->validPassword, $user->password));
    }

    // public function test_user_can_register_successfully_with_web_client()
    // {
    //     $this->app['session']->start();
        
    //     $response = $this->withSession([])->postJson($this->endpoint, [
    //         'name'                  => $this->validName,
    //         'password'              => $this->validPassword,
    //         'password_confirmation' => $this->validPassword,
    //         'national_id'           => $this->validNationalId,
    //         'otp_token'             => $this->createVerifiedOtpToken(),
    //         'client'                => 'web',
    //     ]);

    //     $response->assertStatus(201)->assertJson([
    //         'message' => Lang::get('auth.register.success'),
    //     ]);

    //     $this->assertDatabaseHas('users', [
    //         'phone'       => $this->validPhone,
    //         'name'        => $this->validName,
    //         'national_id' => $this->validNationalId,
    //     ]);

    //     $this->assertAuthenticated('sanctum');
    // }

    // ==================== VALIDATION TESTS ====================

    public function test_registration_fails_when_otp_token_is_missing()
    {
        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $this->validNationalId,
            'client'                => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['otp_token']);
    }

    public function test_registration_fails_when_otp_token_is_invalid()
    {
        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $this->validNationalId,
            'otp_token'             => 'invalid_token_12345678901234567890',
            'client'                => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['otp_token']);
    }

    public function test_registration_fails_when_otp_token_is_expired()
    {
        $token = $this->createVerifiedOtpToken(expired: true);

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
            'client'                => 'mobile',
        ]);

        // Controller returns 422 with a custom message
        $response->assertStatus(422)
            ->assertJson([
                'message' => Lang::get('validation.custom.verification_token.invalid'),
            ]);
    }

    public function test_registration_fails_when_phone_is_already_registered()
    {
        // Create a user with the same phone
        User::create([
            'phone'       => $this->validPhone,
            'name'        => 'Existing User',
            'password'    => Hash::make('password'),
            'national_id' => '0987654321',
        ]);

        $token = $this->createVerifiedOtpToken();

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
            'client'                => 'mobile',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('users', 1); // The existing + the new one with same phone
    }

    public function test_registration_fails_when_national_id_already_registered()
    {
        User::create([
            'phone'       => '09123456788',
            'name'        => 'Existing User',
            'password'    => Hash::make('password'),
            'national_id' => $this->validNationalId,
        ]);

        $token = $this->createVerifiedOtpToken();

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
            'client'                => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['national_id']);
    }

    public function test_registration_fails_when_name_is_missing()
    {
        $token = $this->createVerifiedOtpToken();

        $response = $this->postJson($this->endpoint, [
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
            'client'                => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_registration_fails_when_name_is_too_short()
    {
        $token = $this->createVerifiedOtpToken();

        $response = $this->postJson($this->endpoint, [
            'name'                  => 'Jo',
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
            'client'                => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_registration_fails_when_password_is_too_short()
    {
        $token = $this->createVerifiedOtpToken();

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => 'Pass1',
            'password_confirmation' => 'Pass1',
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
            'client'                => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_when_password_has_no_uppercase()
    {
        $token = $this->createVerifiedOtpToken();

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
            'client'                => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_when_password_has_no_numbers()
    {
        $token = $this->createVerifiedOtpToken();

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => 'PasswordABC',
            'password_confirmation' => 'PasswordABC',
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
            'client'                => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_when_password_confirmation_does_not_match()
    {
        $token = $this->createVerifiedOtpToken();

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => 'DifferentPassword123',
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
            'client'                => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_registration_fails_when_national_id_is_missing()
    {
        $token = $this->createVerifiedOtpToken();

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'otp_token'             => $token,
            'client'                => 'mobile',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['national_id']);
    }

    public function test_registration_fails_when_client_is_invalid()
    {
        $token = $this->createVerifiedOtpToken();

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
            'client'                => 'invalid_client',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client']);
    }

    public function test_registration_fails_when_client_is_missing()
    {
        $token = $this->createVerifiedOtpToken();

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client']);
    }

    // ==================== SANITIZATION TESTS ====================

    public function test_registration_sanitizes_national_id()
    {
        $token = $this->createVerifiedOtpToken();
        $nationalIdWithSpaces = '136 2-_964 441';

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $nationalIdWithSpaces,
            'otp_token'             => $token,
            'client'                => 'mobile',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'phone'       => $this->validPhone,
            'national_id' => '1362964441',
        ]);
    }

    public function test_registration_uses_phone_from_otp_token_not_request()
    {
        $otpPhone = '09123456788';
        $token = $this->createVerifiedOtpToken($otpPhone);

        $response = $this->postJson($this->endpoint, [
            'name'                  => $this->validName,
            'password'              => $this->validPassword,
            'password_confirmation' => $this->validPassword,
            'national_id'           => $this->validNationalId,
            'otp_token'             => $token,
            'client'                => 'mobile',
            'phone'                 => '09223456798',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', [
            'phone' => $otpPhone,
        ]);
    }
}
