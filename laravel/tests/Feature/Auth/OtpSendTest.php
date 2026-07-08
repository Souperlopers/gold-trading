<?php

namespace Tests\Feature\Auth;

use App\Models\OtpCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class OtpSendTest extends TestCase
{
    use RefreshDatabase;

    private string $validPhone = '09309506250';
    private string $purpose = 'registration';
    private string $endpoint = '/api/auth/phone/send-otp';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.otp.timeout', 15);
        Config::set('services.otp.api_key', 'M3dElL6etOQynWunpqCq82rYjRvv46O2KfEc24fb9LHIvafo');
        Config::set('services.otp.url', 'https://api.sms.ir/v1/send/verify');
        Config::set('services.otp.template_id', 173187);
        Config::set('services.otp.resend_wait', 60);
        Config::set('services.otp.expiry', 300);
    }

    public function test_it_sends_otp_successfully()
    {
        Http::fake(['api.sms.ir/*' => Http::response([
            'status' => 1,
            'message' => 'موفق',
            'data' => [
                'messageId' => 89545112,
                'cost' => 1.0,
            ]
        ], 200)]);

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(200)->assertJsonStructure(['message', 'expires_at']);

        $this->assertDatabaseHas('otp_codes', [
            'phone'   => $this->validPhone,
            'purpose' => $this->purpose,
            'used_at' => null,
            'service_response' => '89545112',
        ]);
    }

    public function test_it_prevents_duplicate_otp_within_wait_period()
    {
        $otp = OtpCode::create([
            'phone' => $this->validPhone,
            'code' => '123456',
            'purpose' => $this->purpose,
        ]);

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(429)
            ->assertJson([
                'message' => Lang::get('auth.otp.send.already_sent'),
            ]);

        $this->assertDatabaseCount('otp_codes', 1);
    }

    public function test_it_reuses_existing_otp_after_wait_period_passed()
    {
        Http::fake([
            'api.sms.ir/*' => Http::response([
                'status' => 1,
                'data' => ['messageId' => 99999999]
            ], 200)
        ]);

        $otp = OtpCode::create([
            'phone'   => $this->validPhone,
            'code'    => '123456',
            'purpose' => $this->purpose,
        ]);
        $otp->created_at = now()->subSeconds(Config::get('auth.otp.resend_wait') + 1);
        $otp->save();

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(200);

        // Should reuse existing OTP (not create a new one)
        $this->assertDatabaseCount('otp_codes', 1);
        $this->assertDatabaseHas('otp_codes', [
            'id' => $otp->id,
            'code' => '123456',
        ]);
    }

    public function test_it_creates_new_otp_when_attempts_exceeded()
    {
        Http::fake([
            'api.sms.ir/*' => Http::response([
                'status' => 1,
                'data' => ['messageId' => 88888888]
            ], 200)
        ]);

        $existingOtp = OtpCode::create([
            'phone' => $this->validPhone,
            'code' => '123456',
            'purpose' => $this->purpose,
            'attempts' => 3,
        ]);

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('otp_codes', 2);
    }

    public function test_it_allows_new_otp_after_previous_was_used()
    {
        Http::fake([
            'api.sms.ir/*' => Http::response([
                'status' => 1,
                'data' => ['messageId' => 77777777]
            ], 200)
        ]);

        $otp = OtpCode::create([
            'phone' => $this->validPhone,
            'code' => '123456',
            'purpose' => $this->purpose,
            'used_at' => now(),
        ]);
        $otp->created_at = now()->subSeconds(10);
        $otp->save();

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('otp_codes', 2);
    }

    public function test_it_allows_new_otp_after_previous_expired()
    {
        Http::fake([
            'api.sms.ir/*' => Http::response([
                'status' => 1,
                'data' => ['messageId' => 66666666]
            ], 200)
        ]);

        $otp = OtpCode::create([
            'phone' => $this->validPhone,
            'code' => '123456',
            'purpose' => $this->purpose,
        ]);
        $otp->created_at = now()->subMinutes(Config::get('services.otp.timeout', 5) + 1);
        $otp->save();

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('otp_codes', 2);
    }

    public function test_it_handles_different_purposes_separately()
    {
        Http::fake([
            'api.sms.ir/*' => Http::response([
                'status' => 1,
                'data' => ['messageId' => 55555555]
            ], 200)
        ]);

        $otp = OtpCode::create([
            'phone' => $this->validPhone,
            'code' => '123456',
            'purpose' => 'registration',
        ]);
        $otp->created_at = now()->subSeconds(10);
        $otp->save();

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => 'password_reset',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('otp_codes', 2);
    }

    public function test_it_handles_timeout_gracefully()
    {
        // Simulate a timeout by faking a 500 response with a short timeout
        Config::set('services.otp.timeout', 1);
        Config::set('services.otp.connection_timeout', 1);

        Http::fake([
            'api.sms.ir/*' => Http::response('Connection timeout', 500)
        ]);

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(503)
            ->assertJson([
                'message' => Lang::get('auth.otp.send.fail'),
            ]);
    }

    public function test_it_handles_api_error_response()
    {
        Http::fake([
            'api.sms.ir/*' => Http::response([
                'status' => 0,
                'message' => 'Insufficient credit'
            ], 200)
        ]);

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(503)
            ->assertJson([
                'message' => Lang::get('auth.otp.send.fail'),
            ]);
    }

    public function test_it_handles_http_error_response()
    {
        Http::fake([
            'api.sms.ir/*' => Http::response(['error' => 'Unauthorized'], 401)
        ]);

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(503)
            ->assertJson([
                'message' => Lang::get('auth.otp.send.fail'),
            ]);
    }

    public function test_it_validates_phone_required()
    {
        $response = $this->postJson($this->endpoint, [
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_it_validates_phone_format()
    {
        $response = $this->postJson($this->endpoint, [
            'phone' => '0912345678',
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_it_validates_phone_starts_with_09()
    {
        $response = $this->postJson($this->endpoint, [
            'phone' => '09123456789',
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(200);

        $response = $this->postJson($this->endpoint, [
            'phone' => '99123456789',
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_it_validates_purpose_required()
    {
        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['purpose']);
    }

    public function test_it_validates_purpose_must_be_valid_enum()
    {
        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => 'invalid_purpose',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['purpose']);
    }

    public function test_it_stores_service_response_in_model()
    {
        Http::fake([
            'api.sms.ir/*' => Http::response([
                'status' => 1,
                'data' => ['messageId' => 89545112]
            ], 200)
        ]);

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $otp = OtpCode::first();
        $this->assertNotNull($otp->service_response);
        $this->assertIsInt($otp->service_response);
        $this->assertEquals(89545112, $otp->service_response);
    }

    public function test_it_returns_correct_expiration_time()
    {
        Http::fake([
            'api.sms.ir/*' => Http::response([
                'status' => 1,
                'data' => ['messageId' => 12345]
            ], 200)
        ]);

        Config::set('auth.otp.expiry', 500);

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $expiresAt = $response->json('expires_at');
        $otp = OtpCode::first();

        $expected = $otp->created_at->addSeconds(500)->toISOString();
        $this->assertEquals($expected, $expiresAt);
    }

    public function test_it_sanitizes_phone_number()
    {
        Http::fake([
            'api.sms.ir/*' => Http::response([
                'status' => 1,
                'data' => ['messageId' => 12345]
            ], 200)
        ]);

        $response = $this->postJson($this->endpoint, [
            'phone' => '0912 123 4567',
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('otp_codes', [
            'phone' => '09121234567',
            'purpose' => $this->purpose,
        ]);
    }

    public function test_it_generates_6_digit_code()
    {
        Http::fake([
            'api.sms.ir/*' => Http::response([
                'status' => 1,
                'data' => ['messageId' => 12345]
            ], 200)
        ]);

        $response = $this->postJson($this->endpoint, [
            'phone' => $this->validPhone,
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(200);

        $otp = OtpCode::first();
        $this->assertIsNumeric($otp->code);
        $this->assertEquals(6, strlen($otp->code));
        $this->assertGreaterThanOrEqual(100000, (int) $otp->code);
        $this->assertLessThanOrEqual(999999, (int) $otp->code);
    }

    public function test_it_does_not_allow_requesting_otp_for_same_purpose_within_wait_period_even_with_different_phone()
    {
        $otp = OtpCode::create([
            'phone' => $this->validPhone,
            'code' => '123456',
            'purpose' => $this->purpose,
        ]);
        $otp->created_at = now()->subSeconds(10);
        $otp->save();

        $response = $this->postJson($this->endpoint, [
            'phone' => '09123456788',
            'purpose' => $this->purpose,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseCount('otp_codes', 2);
    }
}
