<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_contains_forgot_password_link(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee(route('password.request'));
    }

    public function test_forgot_password_page_loads_for_guest(): void
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
        $response->assertSee('Forgot Password');
        $response->assertSee('Send Reset Link');
    }

    public function test_authenticated_user_cannot_access_forgot_password_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/forgot-password');
        $response->assertRedirect('/dashboard');
    }

    public function test_reset_link_can_be_requested_with_valid_email(): void
    {
        $user = User::factory()->create(['email' => 'client@example.com']);

        $response = $this->post('/forgot-password', [
            'email' => 'client@example.com',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'client@example.com',
        ]);
    }

    public function test_reset_link_can_be_requested_with_registered_phone(): void
    {
        $user = User::factory()->create([
            'email' => 'phoneuser@example.com',
            'phone' => '03001234567',
        ]);

        $response = $this->post('/forgot-password', [
            'email' => '03001234567',
        ]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'phoneuser@example.com',
        ]);
    }

    public function test_reset_password_page_loads_with_token(): void
    {
        $response = $this->get('/reset-password/sample-test-token?email=client@example.com');
        $response->assertStatus(200);
        $response->assertSee('Set New Password');
        $response->assertSee('sample-test-token');
    }

    public function test_password_can_be_successfully_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'taxpayer@example.com',
            'password' => Hash::make('old-password-123'),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'taxpayer@example.com',
            'password' => 'new-secure-password-456',
            'password_confirmation' => 'new-secure-password-456',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('new-secure-password-456', $user->password));
    }

    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'taxpayer@example.com',
            'password' => Hash::make('old-password-123'),
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'completely-invalid-token',
            'email' => 'taxpayer@example.com',
            'password' => 'new-secure-password-456',
            'password_confirmation' => 'new-secure-password-456',
        ]);

        $response->assertSessionHasErrors('email');
        $user->refresh();
        $this->assertTrue(Hash::check('old-password-123', $user->password));
    }

    public function test_password_requires_minimum_eight_characters(): void
    {
        $user = User::factory()->create([
            'email' => 'taxpayer@example.com',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'taxpayer@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
