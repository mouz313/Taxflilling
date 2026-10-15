<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AtlCheckerTest extends TestCase
{
    use RefreshDatabase;

    public function test_atl_check_validates_13_digit_cnic(): void
    {
        $response = $this->postJson('/check-atl', [
            'cnic' => '12345',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['cnic']);
    }

    public function test_atl_check_returns_honest_unverified_status_and_no_parity_bias(): void
    {
        Http::fake();

        // Odd last digit (1)
        $resOdd = $this->postJson('/check-atl', [
            'cnic' => '42101-1234567-1',
        ]);
        $resOdd->assertStatus(200);
        $dataOdd = $resOdd->json();

        // Even last digit (2)
        $resEven = $this->postJson('/check-atl', [
            'cnic' => '42101-1234567-2',
        ]);
        $resEven->assertStatus(200);
        $dataEven = $resEven->json();

        // Both must be identical UNVERIFIED and null is_active
        $this->assertEquals('UNVERIFIED', $dataOdd['status']);
        $this->assertEquals('UNVERIFIED', $dataEven['status']);
        $this->assertFalse($dataOdd['verified']);
        $this->assertFalse($dataEven['verified']);
        $this->assertNull($dataOdd['is_active']);
        $this->assertNull($dataEven['is_active']);

        // Must not contain hardcoded benefits rates
        $this->assertArrayNotHasKey('benefits', $dataOdd);
        $this->assertArrayNotHasKey('benefits', $dataEven);

        // CNIC must be masked, not plain text
        $this->assertEquals('42101-*******-1', $dataOdd['cnic']);
        $this->assertEquals('42101-*******-2', $dataEven['cnic']);

        // Zero outbound HTTP requests made (no unverified scraping)
        Http::assertNothingSent();
    }

    public function test_atl_check_does_not_save_data_to_database(): void
    {
        $this->postJson('/check-atl', [
            'cnic' => '35202-9988776-5',
        ]);

        $this->assertDatabaseMissing('clients', [
            'cnic' => '35202-9988776-5',
        ]);
    }
}
