<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_track_page_loads_successfully(): void
    {
        $response = $this->get('/track');
        $response->assertStatus(200);
        $response->assertSee('Track Your Tax Return');
    }

    public function test_faq_page_loads_successfully(): void
    {
        $response = $this->get('/faq');
        $response->assertStatus(200);
        $response->assertSee('Tax Knowledge Base');
    }

    public function test_contact_page_loads_successfully(): void
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
        $response->assertSee('Connect with Certified Tax');
    }
}
