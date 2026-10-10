<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consultancy;
use Illuminate\Http\Request;

class CmsSiteContentController extends Controller
{
    /**
     * Show site content and contact information CMS page.
     */
    public function index()
    {
        $consultancy = Consultancy::first() ?? new Consultancy([
            'name' => 'TaxFiler Pakistan',
            'phone' => '+92 300 1234567',
            'whatsapp' => '923001234567',
            'secondary_phone' => '(021) 3456-7890',
            'email' => 'support@taxfiler.pk',
            'address' => 'Suite 402, 4th Floor, Business Avenue, Main Shahrah-e-Faisal, Karachi',
            'secondary_address' => 'Executive Heights, Sector F-7 Markaz, Islamabad',
            'office_hours' => 'Monday - Saturday (9:00 AM - 7:00 PM PKT)',
            'ntn_registration_no' => '9876543-2',
            'default_return_fee' => 2500.00,
            'default_ntn_fee' => 1500.00,
            'default_wealth_statement_fee' => 3000.00,
        ]);

        return view('admin.cms.site.index', compact('consultancy'));
    }

    /**
     * Update site content and contact information.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'whatsapp' => ['required', 'string', 'max:50'],
            'secondary_phone' => ['nullable', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string'],
            'secondary_address' => ['nullable', 'string'],
            'office_hours' => ['nullable', 'string', 'max:255'],
            'ntn_registration_no' => ['nullable', 'string', 'max:50'],
            'default_return_fee' => ['required', 'numeric', 'min:0'],
            'default_ntn_fee' => ['required', 'numeric', 'min:0'],
            'default_wealth_statement_fee' => ['required', 'numeric', 'min:0'],
        ]);

        $consultancy = Consultancy::first();
        if (!$consultancy) {
            $consultancy = new Consultancy();
            $consultancy->slug = 'taxfiler-pakistan';
        }

        $consultancy->fill($validated);
        $consultancy->save();

        return redirect()->route('admin.cms.site.index')->with('status', 'Website contact and firm profile settings saved successfully!');
    }
}
