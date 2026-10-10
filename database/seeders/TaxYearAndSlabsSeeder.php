<?php

namespace Database\Seeders;

use App\Models\Consultancy;
use App\Models\TaxSlab;
use App\Models\TaxYear;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TaxYearAndSlabsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Default Tax Year (2026 - 2027)
        $taxYear = TaxYear::firstOrCreate(
            ['name' => 'Tax Year 2026-2027'],
            [
                'start_date' => '2026-07-01',
                'end_date' => '2027-06-30',
                'filing_deadline' => '2027-09-30',
                'is_active' => true,
            ]
        );

        // 2. Official FBR Salaried Income Tax Slabs (Finance Act 2024)
        $slabs = [
            [
                'taxpayer_type' => 'salaried',
                'min_income' => 0,
                'max_income' => 600000,
                'fixed_tax' => 0,
                'percentage_rate' => 0,
                'exceeding_threshold' => 0,
            ],
            [
                'taxpayer_type' => 'salaried',
                'min_income' => 600001,
                'max_income' => 1200000,
                'fixed_tax' => 0,
                'percentage_rate' => 5,
                'exceeding_threshold' => 600000,
            ],
            [
                'taxpayer_type' => 'salaried',
                'min_income' => 1200001,
                'max_income' => 2200000,
                'fixed_tax' => 30000,
                'percentage_rate' => 15,
                'exceeding_threshold' => 1200000,
            ],
            [
                'taxpayer_type' => 'salaried',
                'min_income' => 2200001,
                'max_income' => 3200000,
                'fixed_tax' => 180000,
                'percentage_rate' => 25,
                'exceeding_threshold' => 2200000,
            ],
            [
                'taxpayer_type' => 'salaried',
                'min_income' => 3200001,
                'max_income' => 4100000,
                'fixed_tax' => 430000,
                'percentage_rate' => 30,
                'exceeding_threshold' => 3200000,
            ],
            [
                'taxpayer_type' => 'salaried',
                'min_income' => 4100001,
                'max_income' => null, // Infinity
                'fixed_tax' => 700000,
                'percentage_rate' => 35,
                'exceeding_threshold' => 4100000,
            ],
        ];

        foreach ($slabs as $slabData) {
            TaxSlab::firstOrCreate(
                [
                    'tax_year_id' => $taxYear->id,
                    'taxpayer_type' => $slabData['taxpayer_type'],
                    'min_income' => $slabData['min_income'],
                ],
                $slabData
            );
        }

        // 3. Create Default Consultancy (Internal Dry Run)
        $consultancy = Consultancy::firstOrCreate(
            ['slug' => 'apex-tax-consultancy'],
            [
                'name' => 'Apex Tax & Corporate Advisory',
                'phone' => '+92 300 1234567',
                'email' => 'admin@apextax.pk',
                'address' => 'Suite 402, Business Avenue, Shahrah-e-Faisal, Karachi',
                'ntn_registration_no' => '7891234-5',
                'default_return_fee' => 2500.00,
                'default_ntn_fee' => 1500.00,
                'is_active' => true,
            ]
        );

        // 4. Create Company Admin User
        User::firstOrCreate(
            ['email' => 'admin@apextax.pk'],
            [
                'name' => 'Consultant Admin',
                'password' => Hash::make('password123'),
                'consultancy_id' => $consultancy->id,
                'role' => 'company',
                'phone' => '03001234567',
                'is_active' => true,
            ]
        );
    }
}
