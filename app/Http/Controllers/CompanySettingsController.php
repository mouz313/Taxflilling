<?php

namespace App\Http\Controllers;

use App\Models\Consultancy;
use Illuminate\Http\Request;

class CompanySettingsController extends Controller
{
    public function show()
    {
        $consultancy = auth()->user()->consultancy ?? Consultancy::first();
        return view('settings.company', compact('consultancy'));
    }

    public function update(Request $request)
    {
        $consultancy = auth()->user()->consultancy ?? Consultancy::first();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'ntn_registration_no' => ['nullable', 'string', 'max:50'],
            'default_return_fee' => ['required', 'numeric', 'min:0'],
            'default_ntn_fee' => ['required', 'numeric', 'min:0'],
            'default_wealth_statement_fee' => ['required', 'numeric', 'min:0'],
        ]);

        $consultancy->update($validated);

        return back()->with('success', 'Consultancy settings aur fee structure kamiyabi se update ho gaya!');
    }
}
