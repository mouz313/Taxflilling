<?php

namespace Tests\Feature;

use App\Models\CallbackRequest;
use App\Models\Consultancy;
use App\Models\ServiceRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_business_services_hub_loads_successfully(): void
    {
        $response = $this->get('/services');

        $response->assertStatus(200);
        $response->assertSee('Our Business & Corporate Services', false);
        $response->assertSee('Individual NTN Registration');
        $response->assertSee('Private Limited Company Registration');
        $response->assertSee('USA LLC');
    }

    public function test_request_for_call_stores_successfully(): void
    {
        $response = $this->post('/services/callback', [
            'name' => 'Kashif Mehmood',
            'phone' => '03009876543',
            'email' => 'kashif@example.com',
            'service_code' => 'secp_pvt_ltd',
            'service_name' => 'Private Limited Company Registration',
            'preferred_time' => 'morning',
            'notes' => 'Mujhe Pvt Ltd company banwani hai software export ke liye.',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('callback_requests', [
            'name' => 'Kashif Mehmood',
            'phone' => '03009876543',
            'service_code' => 'secp_pvt_ltd',
            'preferred_time' => 'morning',
        ]);
    }

    public function test_service_intake_form_application_stores_successfully(): void
    {
        $response = $this->post('/services/apply', [
            'service_code' => 'ntn_individual',
            'applicant_name' => 'Zubair Ahmed',
            'applicant_phone' => '03121234567',
            'applicant_email' => 'zubair@example.com',
            'fields' => [
                'full_name' => 'Zubair Ahmed',
                'cnic' => '42101-9999999-1',
                'mobile' => '03121234567',
                'email' => 'zubair@example.com',
                'electricity_consumer_no' => '04123456789012',
                'residential_address' => 'House 12, Street 4, Gulshan, Karachi',
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('service_requests', [
            'applicant_name' => 'Zubair Ahmed',
            'applicant_email' => 'zubair@example.com',
            'service_code' => 'ntn_individual',
            'status' => 'submitted',
        ]);
    }
}
