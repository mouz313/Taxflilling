<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Consultancy;
use App\Models\ServiceRequest;
use App\Models\TaxReturn;
use App\Models\TaxYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndOwnershipTest extends TestCase
{
    use RefreshDatabase;

    protected Consultancy $consultancy;
    protected TaxYear $taxYear;
    protected string $salt = 'test_secure_salt_123';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.jazzcash.salt', $this->salt);
        Config::set('services.jazzcash.merchant_id', 'MC12345');
        Config::set('services.jazzcash.password', 'pass123');

        $this->consultancy = Consultancy::create([
            'name' => 'Apex Advisory',
            'slug' => 'apex',
            'default_return_fee' => 2500,
        ]);

        $this->taxYear = TaxYear::create([
            'name' => '2026',
            'start_date' => '2025-07-01',
            'end_date' => '2026-06-30',
            'filing_deadline' => '2026-09-30',
            'is_active' => true,
        ]);
    }

    protected function generateJazzCashHash(array $payload, ?string $salt = null): string
    {
        $salt = $salt ?? $this->salt;
        unset($payload['pp_SecureHash']);
        ksort($payload);
        $hashString = $salt;
        foreach ($payload as $k => $v) {
            if ($v !== '' && str_starts_with($k, 'pp_')) {
                $hashString .= '&' . $v;
            }
        }
        return strtoupper(hash_hmac('sha256', $hashString, $salt));
    }

    public function test_unauthenticated_user_cannot_mark_service_request_paid(): void
    {
        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Ali Khan',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'ali@example.com',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        // Attempting to post to old /services/{id}/pay should fail (route removed)
        $response = $this->post("/services/{$order->id}/pay", [
            'payment_method' => 'bank_transfer',
        ]);
        $response->assertStatus(404);

        // Attempting mark-paid without company authentication fails with redirect to login
        $markPaidResponse = $this->post("/services/{$order->id}/mark-paid");
        $markPaidResponse->assertRedirect('/login');

        // Order remains pending
        $this->assertEquals('pending', $order->fresh()->payment_status);
    }

    public function test_submitting_payment_proof_does_not_mark_order_paid_and_requires_ownership(): void
    {
        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Ali Khan',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'ali@example.com',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        // Mismatched phone last 4 digits fails with 404
        $failResponse = $this->post("/services/{$order->id}/proof", [
            'tracking_number' => $order->tracking_number,
            'phone_last4' => '9999',
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'IBFT-123456',
        ]);
        $failResponse->assertStatus(404);

        // Valid phone last 4 digits succeeds
        $response = $this->post("/services/{$order->id}/proof", [
            'tracking_number' => $order->tracking_number,
            'phone_last4' => '4567',
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'IBFT-123456',
        ]);

        $response->assertSessionHas('success');
        $freshOrder = $order->fresh();
        $this->assertEquals('pending_verification', $freshOrder->payment_status);
        $this->assertEquals('IBFT-123456', $freshOrder->payment_reference);
        $this->assertNotEquals('paid', $freshOrder->payment_status);
    }

    public function test_proof_submission_on_already_paid_order_aborts_409(): void
    {
        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Ali Khan',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'ali@example.com',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500,
            'status' => 'submitted',
            'payment_status' => 'paid',
        ]);

        $response = $this->post("/services/{$order->id}/proof", [
            'tracking_number' => $order->tracking_number,
            'phone_last4' => '4567',
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'IBFT-123456',
        ]);

        $response->assertStatus(409);
    }

    public function test_proof_file_stored_on_private_disk_and_viewable_only_by_staff(): void
    {
        Storage::fake('local');

        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Ali Khan',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'ali@example.com',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        $tempFile = tempnam(sys_get_temp_dir(), 'proof');
        file_put_contents($tempFile, "%PDF-1.4\n1 0 obj<</Type/Catalog>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF");
        $file = new UploadedFile($tempFile, 'receipt.pdf', 'application/pdf', null, true);

        $response = $this->from("/services/{$order->id}/checkout")->post("/services/{$order->id}/proof", [
            'tracking_number' => $order->tracking_number,
            'phone_last4' => '4567',
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'IBFT-123456',
            'proof_file' => $file,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $freshOrder = $order->fresh();
        $this->assertNotNull($freshOrder->payment_proof_path);
        Storage::disk('local')->assertExists($freshOrder->payment_proof_path);

        // Guest cannot access proof-file route
        $guestView = $this->get("/services/{$order->id}/proof-file");
        $guestView->assertRedirect('/login');

        // Staff can access proof-file route
        $staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'phone' => '03000000000',
            'role' => 'company',
            'password' => Hash::make('Secret123!'),
            'consultancy_id' => $this->consultancy->id,
            'is_active' => true,
        ]);

        $staffView = $this->actingAs($staff)->get("/services/{$order->id}/proof-file");
        $staffView->assertStatus(200);
    }

    public function test_client_cannot_access_or_edit_another_clients_return_idor(): void
    {
        // Client A
        $userA = User::create([
            'name' => 'Client A',
            'email' => 'clienta@example.com',
            'phone' => '03001111111',
            'role' => 'client',
            'password' => Hash::make('Password123!'),
            'consultancy_id' => $this->consultancy->id,
            'is_active' => true,
        ]);
        $clientA = Client::create([
            'consultancy_id' => $this->consultancy->id,
            'user_id' => $userA->id,
            'name' => 'Client A',
            'cnic' => '42101-1111111-1',
            'phone' => '03001111111',
            'email' => 'clienta@example.com',
        ]);
        $returnA = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $clientA->id,
            'tax_year_id' => $this->taxYear->id,
            'filing_type' => 'original',
            'status' => 'draft',
        ]);

        // Client B
        $userB = User::create([
            'name' => 'Client B',
            'email' => 'clientb@example.com',
            'phone' => '03002222222',
            'role' => 'client',
            'password' => Hash::make('Password123!'),
            'consultancy_id' => $this->consultancy->id,
            'is_active' => true,
        ]);
        $clientB = Client::create([
            'consultancy_id' => $this->consultancy->id,
            'user_id' => $userB->id,
            'name' => 'Client B',
            'cnic' => '42101-2222222-2',
            'phone' => '03002222222',
            'email' => 'clientb@example.com',
        ]);
        $returnB = TaxReturn::create([
            'consultancy_id' => $this->consultancy->id,
            'client_id' => $clientB->id,
            'tax_year_id' => $this->taxYear->id,
            'filing_type' => 'original',
            'status' => 'draft',
        ]);

        // Client A tries to view Client B's wizard
        $this->actingAs($userA);
        $wizardResponse = $this->get("/returns/{$returnB->id}/wizard");
        $wizardResponse->assertStatus(403);

        // Client A tries to add income to Client B's return
        $incomeResponse = $this->post("/returns/{$returnB->id}/income", [
            'source_type' => 'salary',
            'gross_amount' => 500000,
        ]);
        $incomeResponse->assertStatus(403);

        // Client A tries to download Client B's PDF dossier
        $pdfResponse = $this->get("/returns/{$returnB->id}/pdf");
        $pdfResponse->assertStatus(403);

        // Client A can view their own return wizard
        $ownResponse = $this->get("/returns/{$returnA->id}/wizard");
        $ownResponse->assertStatus(200);
    }

    public function test_payment_callback_rejects_non_jazzcash_gateways(): void
    {
        $response = $this->post('/payment/callback/easypaisa', [
            'tracking_number' => '123456789',
        ]);
        // Route regex limits {gateway} to jazzcash, or controller rejects
        $this->assertTrue(in_array($response->status(), [404, 501]));
    }

    public function test_payment_callback_rejects_missing_salt_fail_closed(): void
    {
        Config::set('services.jazzcash.salt', null);

        $response = $this->postJson('/payment/callback/jazzcash', [
            'pp_BillReference' => 'ABC',
            'pp_SecureHash' => 'SOME_HASH',
        ]);

        $response->assertStatus(403);
    }

    public function test_payment_callback_rejects_invalid_hash_signature(): void
    {
        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Test Applicant',
            'applicant_phone' => '03009999999',
            'applicant_email' => 'applicant@example.com',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        $response = $this->postJson('/payment/callback/jazzcash', [
            'pp_BillReference' => $order->tracking_number,
            'pp_ResponseCode' => '000',
            'pp_Amount' => 250000,
            'pp_TxnRefNo' => 'TXN-FAKE-123',
            'pp_SecureHash' => 'INVALID_HASH_VALUE_HERE',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('pending', $order->fresh()->payment_status);
    }

    public function test_payment_callback_rejects_amount_mismatch(): void
    {
        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Test Applicant',
            'applicant_phone' => '03009999999',
            'applicant_email' => 'applicant@example.com',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500, // PKR 2500 = 250000 paisa
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        $payload = [
            'pp_Amount' => '100000', // Only 1000 PKR instead of 2500
            'pp_BillReference' => $order->tracking_number,
            'pp_ResponseCode' => '000',
            'pp_TxnRefNo' => 'TXN-WRONG-AMT',
        ];
        $payload['pp_SecureHash'] = $this->generateJazzCashHash($payload);

        $response = $this->postJson('/payment/callback/jazzcash', $payload);
        $response->assertStatus(422);
        $this->assertEquals('pending', $order->fresh()->payment_status);
    }

    public function test_payment_callback_verifies_valid_hash_signature_and_is_idempotent(): void
    {
        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Test Applicant',
            'applicant_phone' => '03009999999',
            'applicant_email' => 'applicant@example.com',
            'service_code' => 'ntn_individual',
            'service_name' => 'NTN Registration',
            'fee_amount' => 2500,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        $payload = [
            'pp_Amount' => '250000',
            'pp_BillReference' => $order->tracking_number,
            'pp_ResponseCode' => '000',
            'pp_TxnRefNo' => 'TXN-REAL-789',
        ];
        $payload['pp_SecureHash'] = $this->generateJazzCashHash($payload);

        // First callback succeeds
        $response = $this->postJson('/payment/callback/jazzcash', $payload);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'reference' => 'TXN-REAL-789']);

        $freshOrder = $order->fresh();
        $this->assertEquals('paid', $freshOrder->payment_status);
        $this->assertEquals('in_review', $freshOrder->status);
        $this->assertEquals('TXN-REAL-789', $freshOrder->payment_reference);

        // Duplicate concurrent callback returns already_processed
        $duplicateResponse = $this->postJson('/payment/callback/jazzcash', $payload);
        $duplicateResponse->assertStatus(200);
        $duplicateResponse->assertJson(['status' => 'already_processed']);
    }

    public function test_guest_search_endpoint_returns_empty_results(): void
    {
        ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Kashif Mehmood',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'kashif@example.com',
            'service_code' => 'secp_pvt_ltd',
            'service_name' => 'Company Registration',
            'fee_amount' => 15000,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        $response = $this->getJson('/track/search?q=Kashif');
        $response->assertStatus(200);
        $this->assertEquals([], $response->json('results'));
    }

    public function test_track_page_requires_exact_number_and_phone_verification(): void
    {
        $order = ServiceRequest::create([
            'consultancy_id' => $this->consultancy->id,
            'applicant_name' => 'Kashif Mehmood',
            'applicant_phone' => '03001234567',
            'applicant_email' => 'kashif@example.com',
            'service_code' => 'secp_pvt_ltd',
            'service_name' => 'Company Registration',
            'fee_amount' => 15000,
            'status' => 'submitted',
            'payment_status' => 'pending',
        ]);

        // Wrong phone last 4 digits fails to match
        $mismatchResponse = $this->get('/track?q=' . $order->tracking_number . '&verify=9999');
        $mismatchResponse->assertStatus(200);
        $mismatchResponse->assertSee('Record nahi mila ya verification ghalat hai.');

        // Correct phone last 4 digits matches
        $matchResponse = $this->get('/track?q=' . $order->tracking_number . '&verify=4567');
        $matchResponse->assertStatus(200);
        $matchResponse->assertSee($order->tracking_number);
        $matchResponse->assertSee('Company Registration');
    }

    public function test_check_cnic_returns_no_pii(): void
    {
        $client = Client::create([
            'consultancy_id' => $this->consultancy->id,
            'name' => 'Super Secret Client',
            'cnic' => '42101-1234567-1',
            'ntn' => '7654321-0',
            'phone' => '03001234567',
            'email' => 'secret@example.com',
        ]);

        $response = $this->postJson('/services/check-cnic', [
            'cnic' => '42101-1234567-1',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        // Must not contain name, email, NTN, or existing status
        $this->assertArrayNotHasKey('client', $data);
        $this->assertArrayNotHasKey('name', $data);
        $this->assertArrayNotHasKey('ntn', $data);
        $this->assertArrayNotHasKey('exists', $data);
        $this->assertTrue($data['valid']);
    }

    public function test_client_where_cnic_matches_via_blind_index(): void
    {
        $client = Client::create([
            'consultancy_id' => $this->consultancy->id,
            'name' => 'Ahmed Raza',
            'cnic' => '42101-9876543-1',
            'phone' => '03121234567',
            'email' => 'ahmed@example.com',
        ]);

        $found = Client::whereCnic('42101-9876543-1')->first();
        $this->assertNotNull($found);
        $this->assertEquals($client->id, $found->id);

        // Also works with unformatted 13 digits
        $foundPlain = Client::whereCnic('4210198765431')->first();
        $this->assertNotNull($foundPlain);
        $this->assertEquals($client->id, $foundPlain->id);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $inactive = User::create([
            'name' => 'Deactivated User',
            'email' => 'deactive@example.com',
            'phone' => '03009999000',
            'role' => 'client',
            'password' => Hash::make('Secret123!'),
            'consultancy_id' => $this->consultancy->id,
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'deactive@example.com',
            'password' => 'Secret123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
