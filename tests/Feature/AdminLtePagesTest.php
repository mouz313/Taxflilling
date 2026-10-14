<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLtePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function getCompanyUser(): User
    {
        $user = User::where('role', 'company')->first();
        if (!$user) {
            $user = User::factory()->create([
                'role' => 'company',
                'name' => 'Test Tax Consultant',
                'email' => 'consultant@taxfiler.pk',
            ]);
        }
        return $user;
    }

    public function test_all_admin_lte_pages_render_successfully_with_full_width(): void
    {
        $user = $this->getCompanyUser();

        $routes = [
            '/dashboard',
            '/returns',
            '/clients',
            '/admin/corporate-orders',
            '/admin/call-requests',
            '/admin/fbr-verification',
            '/admin/cms/seo',
            '/admin/cms/services',
            '/settings/company',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($user)->get($route);

            $response->assertStatus(200, "Failed asserting route {$route} returns 200 OK");
            $response->assertSee('adminlte.min.css', false);
            $response->assertSee('main-sidebar', false);
            $response->assertSee('container-fluid', false);
            $response->assertSee('main-header', false);
        }
    }
}
