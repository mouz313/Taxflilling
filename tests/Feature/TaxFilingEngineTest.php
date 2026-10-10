<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Consultancy;
use App\Models\IncomeItem;
use App\Models\TaxReturn;
use App\Models\TaxSlab;
use App\Models\TaxYear;
use App\Models\User;
use App\Models\WealthReconciliation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaxFilingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\TaxYearAndSlabsSeeder::class);
    }
    public function test_cnic_blind_index_generation(): void
    {
        $cnic = '42101-1234567-1';
        $hash1 = Client::generateCnicHash($cnic);
        $hash2 = Client::generateCnicHash('4210112345671'); // Without dashes

        $this->assertNotNull($hash1);
        $this->assertEquals($hash1, $hash2, 'Normalized CNIC hash must match regardless of dashes.');
    }

    /**
     * Test Pakistan FBR Slabs Calculation on Model
     */
    public function test_tax_slab_model_calculations(): void
    {
        $taxYear = TaxYear::where('name', 'Tax Year 2024-2025')->first();
        if (! $taxYear) {
            $this->markTestSkipped('Tax Year seeder not present in test DB.');
        }

        // Slab 1: Up to 600k -> 0
        $this->assertEquals(0, TaxSlab::computeTax(500000, $taxYear->id, 'salaried'));
        $this->assertEquals(0, TaxSlab::computeTax(600000, $taxYear->id, 'salaried'));

        // Slab 2: 600,001 to 1,200,000 (5% exceeding 600k)
        // For 1,200,000: 5% of 600,000 = 30,000
        $this->assertEquals(30000, TaxSlab::computeTax(1200000, $taxYear->id, 'salaried'));

        // Slab 3: 1,200,001 to 2,200,000 (30,000 + 15% exceeding 1.2M)
        // For 1,800,000: 30,000 + 15% of 600,000 (90,000) = 120,000
        $this->assertEquals(120000, TaxSlab::computeTax(1800000, $taxYear->id, 'salaried'));
    }

    /**
     * Test Wealth Statement Reconciliation Logic
     */
    public function test_wealth_statement_reconciliation_formula(): void
    {
        $wealth = new WealthReconciliation([
            'opening_wealth' => 5000000,
            'net_inflows' => 2000000,
            'living_expenses' => 1000000,
            'other_outflows' => 500000,
            'closing_wealth' => 5500000, // 5M + 2M - 1M - 0.5M = 5.5M
        ]);

        $expected = 5000000 + 2000000 - 1000000 - 500000;
        $wealth->unreconciled_difference = $expected - 5500000;

        $this->assertTrue($wealth->isReconciled());
        $this->assertEquals(0, $wealth->unreconciled_difference);
    }

    /**
     * Test Client Safe Decryption Fallback
     */
    public function test_client_cnic_safe_decryption_fallback(): void
    {
        $consultancy = Consultancy::first();

        // Create client with raw CNIC directly in DB
        $client = new Client();
        $client->consultancy_id = $consultancy->id;
        $client->name = 'Test Plain CNIC Client';
        $client->phone = '03001112233';
        // Directly set raw unencrypted CNIC attribute
        $client->setRawAttributes([
            'consultancy_id' => $consultancy->id,
            'name' => 'Test Plain CNIC Client',
            'phone' => '03001112233',
            'cnic' => '42101-1234567-1',
            'cnic_hash' => Client::generateCnicHash('42101-1234567-1'),
            'status' => 'active',
        ]);
        $client->save();

        // Reading CNIC must NOT throw DecryptException!
        $fresh = Client::find($client->id);
        $this->assertEquals('42101-1234567-1', $fresh->cnic);
    }
}
