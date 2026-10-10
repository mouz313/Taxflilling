<?php

namespace Database\Seeders;

use App\Models\BusinessServiceItem;
use App\Models\Consultancy;
use App\Models\Faq;
use App\Models\ServiceRequest;
use Illuminate\Database\Seeder;

class CmsInitialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Business Services from ServiceRequest catalog
        $defaultCatalog = ServiceRequest::getCatalog();
        $order = 1;
        foreach ($defaultCatalog as $code => $item) {
            BusinessServiceItem::updateOrCreate(
                ['code' => $code],
                [
                    'category' => $item['category'] ?? 'income',
                    'category_name' => $item['category_name'] ?? 'Income Tax Return',
                    'heading' => $item['heading'] ?? ucfirst($code),
                    'urdu' => $item['urdu'] ?? null,
                    'icon' => $item['icon'] ?? '💼',
                    'fee' => $item['fee'] ?? 0.00,
                    'currency' => $item['currency'] ?? 'Rs.',
                    'turnaround' => $item['turnaround'] ?? '1 - 2 Working Days',
                    'popular' => $item['popular'] ?? false,
                    'is_active' => true,
                    'desc' => $item['desc'] ?? '',
                    'requirements' => $item['requirements'] ?? [],
                    'form_fields' => $item['form_fields'] ?? [],
                    'sort_order' => $order++,
                ]
            );
        }

        // 2. Seed FAQs
        $faqs = [
            [
                'category' => 'filer_benefits',
                'question' => 'Filer banne ke bunyadi faiday kya hain aur Non-Filer par kya nuqsanat hain?',
                'urdu_question' => 'Filer vs Non-Filer Faiday aur Nuqsanat',
                'answer' => "<p>FBR Active Taxpayer List (ATL) mein aane ke bad aap par lagging wala <strong>50% to 100% double withholding tax</strong> khatam ho jata hai:</p><ul class=\"list-disc pl-5 space-y-1 text-xs mt-2\"><li><strong>Bank Cash Withdrawal:</strong> Non-filers par 0.6% tax lagta hai, filers par 0%.</li><li><strong>Vehicle Registration & Token Tax:</strong> Gari khareedne par non-filers se lakhon rupay extra advance tax liya jata hai.</li><li><strong>Property Sale & Purchase:</strong> Filers par 3% withholding tax hai, jabke non-filers par yeh 10.5% tak pohnchta hai.</li><li><strong>Bank Profit & Dividends:</strong> Filers par 15% tax kat-ta hai, non-filers par 30% double tax.</li></ul>",
                'sort_order' => 1,
            ],
            [
                'category' => 'salaried',
                'question' => 'Agar meri salary se company ne tax kaat liya hai, to kya mujhe return file karna zaroori hai?',
                'urdu_question' => 'Tankhwah Daar Afraad Ka Tax Return Lazmi Hai?',
                'answer' => "<p><strong>Jee haan, bilkul lazmi hai!</strong> Company sirf aapka tax Section 149 ke tehat FBR ko jama karwati hai, lekin jab tak aap Section 114(1) ke tehat apna Annual Income Tax Return file nahi karte, FBR aapko Active Filer list mein shamil nahi karta.</p><p class=\"mt-2\">TaxFiler par aap sirf apni Salary Slip upload karte hain aur hum aapka official CPR receipt generate kar ke FBR ATL status activate kar dete hain.</p>",
                'sort_order' => 2,
            ],
            [
                'category' => 'wealth',
                'question' => 'Wealth Statement (Section 116) kya hai aur Assets ka hisaab kaise diya jata hai?',
                'urdu_question' => 'Asason aur Kharchon Ka Hisaab (Wealth Statement)',
                'answer' => "<p>FBR har Pakistani citizen se tax return ke sath unke asason (property, bank balance, gari, sona) aur saalana kharchon ka record maangta hai jise Wealth Statement kehte hain.</p><p class=\"mt-2\">Hamara automated smart wizard aapke Opening Wealth + Income minus Kharchay ko automatically balance karta hai taake reconciliation difference <strong>0</strong> aye aur FBR se koi audit notice na aye.</p>",
                'sort_order' => 3,
            ],
            [
                'category' => 'business_ntn',
                'question' => 'NTN banwane ke liye kin documents ki zaroorat hoti hai aur kitna waqt lagta hai?',
                'urdu_question' => 'NTN Banwane Ke Zaroori Documents',
                'answer' => "<p>Individual NTN ke liye sirf 3 cheezon ki zaroorat hoti hai:</p><ul class=\"list-disc pl-5 space-y-1 text-xs mt-2\"><li>Original CNIC ki front aur back photo</li><li>Registered mobile number jo aapke CNIC par registered ho</li><li>Ghar ya office ka bijli ka bill (address verification ke liye)</li></ul><p class=\"mt-2\">TaxFiler par submit karne ke bad <strong>24 ghanton ke andar</strong> aapka official FBR NTN certificate email aur WhatsApp par deliver ho jata hai.</p>",
                'sort_order' => 4,
            ],
            [
                'category' => 'salaried',
                'question' => 'Tax Year 2026-27 ke FBR Salaried Tax Slabs kya hain?',
                'urdu_question' => 'Finance Act 2026-27 Salaried Tax Slabs',
                'answer' => "<p>Official Finance Act ke tehat salaried tax slabs darj zail hain:</p><div class=\"overflow-x-auto my-2\"><table class=\"w-full text-xs text-left border border-slate-200\"><thead class=\"bg-slate-50 font-bold\"><tr><th class=\"p-2 border\">Annual Income (PKR)</th><th class=\"p-2 border\">Tax Rate</th></tr></thead><tbody><tr><td class=\"p-2 border\">0 se 600,000</td><td class=\"p-2 border font-bold text-emerald-700\">0% (Nil)</td></tr><tr><td class=\"p-2 border\">600,001 se 1,200,000</td><td class=\"p-2 border\">5% of amount exceeding 600,000</td></tr><tr><td class=\"p-2 border\">1,200,001 se 2,200,000</td><td class=\"p-2 border\">PKR 30,000 + 15% exceeding 1.2M</td></tr><tr><td class=\"p-2 border\">2,200,001 se 3,200,000</td><td class=\"p-2 border\">PKR 180,000 + 25% exceeding 2.2M</td></tr><tr><td class=\"p-2 border\">3,200,001 se 4,100,000</td><td class=\"p-2 border\">PKR 430,000 + 30% exceeding 3.2M</td></tr><tr><td class=\"p-2 border\">4,100,000 se upar</td><td class=\"p-2 border\">PKR 700,000 + 35% exceeding 4.1M</td></tr></tbody></table></div>",
                'sort_order' => 5,
            ],
        ];

        foreach ($faqs as $faq) {
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                $faq
            );
        }

        // 3. Ensure Consultancy Info is up to date
        $consultancy = Consultancy::first();
        if ($consultancy) {
            $consultancy->update([
                'phone' => $consultancy->phone ?: '+92 300 1234567',
                'whatsapp' => $consultancy->whatsapp ?: '923001234567',
                'secondary_phone' => $consultancy->secondary_phone ?: '(021) 3456-7890',
                'address' => $consultancy->address ?: 'Suite 402, 4th Floor, Business Avenue, Main Shahrah-e-Faisal, Karachi',
                'secondary_address' => $consultancy->secondary_address ?: 'Executive Heights, Sector F-7 Markaz, Islamabad',
                'office_hours' => $consultancy->office_hours ?: 'Monday - Saturday (9:00 AM - 7:00 PM PKT)',
            ]);
        }
    }
}
