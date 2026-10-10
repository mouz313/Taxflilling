<?php

namespace Tests\Feature;

use App\Models\AtlChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_atl_check_returns_successful_verification(): void
    {
        $response = $this->postJson('/check-atl', [
            'cnic' => '42101-1234567-1',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'api_connected' => true,
            'cnic' => '42101-1234567-1',
        ]);

        $data = $response->json();
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('benefits', $data);
        $this->assertArrayHasKey('action_needed', $data);
    }

    public function test_atl_check_does_not_save_data_to_database(): void
    {
        // Verify database is completely untouched by ATL check
        $this->postJson('/check-atl', [
            'cnic' => '35202-9988776-5',
        ]);

        // AtlChecker model has no table and doesn't write to DB
        $this->assertDatabaseMissing('clients', [
            'cnic' => '35202-9988776-5',
        ]);
    }
}
