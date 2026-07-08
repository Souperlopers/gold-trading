<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class CheckPhoneTest extends TestCase
{
    use RefreshDatabase;

    private string $endpoint = '/api/auth/phone';

    public function test_phone_is_available()
    {
        $response = $this->getJson($this->endpoint . '?' . http_build_query([
            'phone' => '09123456789'
        ]));

        $response->assertOk()->assertJson([
            'available' => true,
        ]);
    }

    public function test_phone_is_taken()
    {
        User::factory()->create([
            'phone' => '09123456789',
            'national_id' => '1362964441',
        ]);

        $response = $this->getJson($this->endpoint . '?' . http_build_query([
            'phone' => '09123456789'
        ]));

        $response->assertOk()->assertJson([
            'available' => false,
        ]);
    }

    public function test_invalid_phone_format_is_rejected()
    {
        $response = $this->getJson($this->endpoint . '?' . http_build_query([
            'phone' => '12345'
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_empty_phone_is_rejected()
    {
        $response = $this->getJson($this->endpoint . '?' . http_build_query([
            'phone' => ''
        ]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_missing_phone_field_is_rejected()
    {
        $response = $this->getJson($this->endpoint);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['phone']);
    }

    public function test_sanitisation_works_correctly()
    {
        // Send phone with spaces, dashes, etc.
        $response = $this->getJson($this->endpoint . '?' . http_build_query([
            'phone' => '+98 915 123 4567',
        ]));

        // The sanitised number should be 09151234567
        $response->assertOk()->assertJson(['available' => true]);

        // Now create a user with the sanitised number
        User::factory()->create(['phone' => '09151234567']);

        $response = $this->getJson($this->endpoint . '?' . http_build_query([
            'phone' => '+98 915 123 4567',
        ]));

        $response->assertOk()->assertJson(['available' => false]);
    }
}
