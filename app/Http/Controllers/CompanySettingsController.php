<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Consultancy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class CompanySettingsController extends Controller
{
    /**
     * Show the company and system settings page.
     */
    public function show(Request $request)
    {
        $user = auth()->user();
        $consultancy = $user->consultancy ?? Consultancy::first();
        $tab = $request->get('tab', 'profile');

        $settings = AppSetting::all()->pluck('value', 'key')->toArray();

        return view('settings.company', compact('consultancy', 'user', 'tab', 'settings'));
    }

    /**
     * Handle updates for profile, branding, gateways, email, firebase.
     */
    public function update(Request $request)
    {
        $section = $request->input('section', 'profile');
        $user = auth()->user();
        $consultancy = $user->consultancy ?? Consultancy::first();

        switch ($section) {
            case 'profile':
                $validated = $request->validate([
                    'name' => ['required', 'string', 'max:255'],
                    'phone' => ['nullable', 'string', 'max:30'],
                    'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
                    'new_password' => ['nullable', 'confirmed', Password::min(8)],
                    'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
                ]);

                $user->name = $validated['name'];
                $user->phone = $validated['phone'] ?? $user->phone;

                if (!empty($validated['new_password'])) {
                    $user->password = Hash::make($validated['new_password']);
                }

                if ($request->hasFile('avatar')) {
                    if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                        Storage::disk('public')->delete($user->profile_photo_path);
                    }
                    $path = $request->file('avatar')->store('avatars', 'public');
                    $user->profile_photo_path = $path;
                }

                $user->save();
                return back()->with('tab', 'profile')->with('status', 'User Profile & Security settings updated successfully!');

            case 'branding':
                $validated = $request->validate([
                    'firm_name' => ['required', 'string', 'max:255'],
                    'firm_tagline' => ['nullable', 'string', 'max:255'],
                    'firm_phone' => ['nullable', 'string', 'max:50'],
                    'firm_whatsapp' => ['nullable', 'string', 'max:50'],
                    'firm_email' => ['nullable', 'email', 'max:255'],
                    'firm_address' => ['nullable', 'string', 'max:500'],
                    'ntn_registration_no' => ['nullable', 'string', 'max:50'],
                    'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg', 'max:2048'],
                ]);

                if ($consultancy) {
                    $consultancy->update([
                        'name' => $validated['firm_name'],
                        'phone' => $validated['firm_phone'] ?? $consultancy->phone,
                        'email' => $validated['firm_email'] ?? $consultancy->email,
                        'address' => $validated['firm_address'] ?? $consultancy->address,
                        'ntn_registration_no' => $validated['ntn_registration_no'] ?? $consultancy->ntn_registration_no,
                    ]);

                    if ($request->hasFile('logo')) {
                        if ($consultancy->logo_path && Storage::disk('public')->exists($consultancy->logo_path)) {
                            Storage::disk('public')->delete($consultancy->logo_path);
                        }
                        $logoPath = $request->file('logo')->store('logos', 'public');
                        $consultancy->logo_path = $logoPath;
                        $consultancy->save();
                        AppSetting::set('firm_logo', $logoPath, 'branding');
                    }
                }

                AppSetting::set('firm_name', $validated['firm_name'], 'branding');
                AppSetting::set('firm_tagline', $validated['firm_tagline'] ?? '', 'branding');
                AppSetting::set('firm_phone', $validated['firm_phone'] ?? '', 'branding');
                AppSetting::set('firm_whatsapp', $validated['firm_whatsapp'] ?? '', 'branding');
                AppSetting::set('firm_email', $validated['firm_email'] ?? '', 'branding');
                AppSetting::set('firm_address', $validated['firm_address'] ?? '', 'branding');

                return back()->with('tab', 'branding')->with('status', 'Firm profile & branding saved!');

            case 'gateways':
                $validated = $request->validate([
                    'bank_title' => ['nullable', 'string', 'max:255'],
                    'bank_name' => ['nullable', 'string', 'max:255'],
                    'bank_account' => ['nullable', 'string', 'max:100'],
                    'bank_iban' => ['nullable', 'string', 'max:100'],
                    'raast_id' => ['nullable', 'string', 'max:50'],
                    'jazzcash_mobile' => ['nullable', 'string', 'max:50'],
                    'easypaisa_mobile' => ['nullable', 'string', 'max:50'],
                    'online_gateway' => ['nullable', 'string', 'max:50'],
                    'online_gateway_key' => ['nullable', 'string', 'max:255'],
                    'online_gateway_secret' => ['nullable', 'string', 'max:255'],
                ]);

                foreach ($validated as $key => $val) {
                    AppSetting::set($key, $val ?? '', 'payments');
                }

                return back()->with('tab', 'gateways')->with('status', 'Payment Gateways & Manual Accounts updated!');

            case 'email':
                $validated = $request->validate([
                    'mail_mailer' => ['nullable', 'string', 'max:50'],
                    'mail_host' => ['nullable', 'string', 'max:255'],
                    'mail_port' => ['nullable', 'string', 'max:10'],
                    'mail_username' => ['nullable', 'string', 'max:255'],
                    'mail_password' => ['nullable', 'string', 'max:255'],
                    'mail_encryption' => ['nullable', 'string', 'max:20'],
                    'mail_from_address' => ['nullable', 'email', 'max:255'],
                    'mail_from_name' => ['nullable', 'string', 'max:255'],
                ]);

                foreach ($validated as $key => $val) {
                    AppSetting::set($key, $val ?? '', 'email');
                }

                return back()->with('tab', 'email')->with('status', 'Email SMTP settings saved!');

            case 'firebase':
                $validated = $request->validate([
                    'firebase_api_key' => ['nullable', 'string', 'max:255'],
                    'firebase_auth_domain' => ['nullable', 'string', 'max:255'],
                    'firebase_project_id' => ['nullable', 'string', 'max:255'],
                    'firebase_app_id' => ['nullable', 'string', 'max:255'],
                    'google_login_enabled' => ['nullable', 'boolean'],
                ]);

                AppSetting::set('firebase_api_key', $validated['firebase_api_key'] ?? '', 'firebase');
                AppSetting::set('firebase_auth_domain', $validated['firebase_auth_domain'] ?? '', 'firebase');
                AppSetting::set('firebase_project_id', $validated['firebase_project_id'] ?? '', 'firebase');
                AppSetting::set('firebase_app_id', $validated['firebase_app_id'] ?? '', 'firebase');
                AppSetting::set('google_login_enabled', $request->boolean('google_login_enabled') ? '1' : '0', 'firebase');

                return back()->with('tab', 'firebase')->with('status', 'Firebase & Google Login settings saved!');

            default:
                return back()->with('status', 'Settings updated!');
        }
    }
}
