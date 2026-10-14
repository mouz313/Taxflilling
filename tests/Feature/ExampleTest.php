<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test the landing page loads successfully with rich SEO metadata
     */
    public function test_landing_page_loads_successfully_with_seo(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Tax & Corporate Advisory', false);
        $response->assertSee('schema.org', false);
        $response->assertSee('TaxConsultant', false);
    }

    /**
     * Test the login page loads successfully
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }
}
