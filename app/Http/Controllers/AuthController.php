<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Consultancy;
use App\Models\TaxReturn;
use App\Models\TaxYear;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $loginField = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$loginField => $request->email, 'password' => $request->password], $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (! Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Aapka account non-active hai. Barah-e-karam management se rabta karein.',
                ]);
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Khush Amdeed! Aap kamiyabi se login ho gaye hain.');
        }

        throw ValidationException::withMessages([
            'email' => 'Diye gaye credentials durust nahi hain.',
        ]);
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'name' => ['nullable', 'string', 'max:255'],
            'cnic' => ['required', 'string'],
            'filer_type' => ['nullable', 'in:fresh_filer,already_filer'],
        ]);

        $consultancy = Consultancy::first() ?? Consultancy::create([
            'name' => 'Apex Tax & Corporate Advisory',
            'slug' => 'apex-tax',
            'default_return_fee' => 2500.00,
        ]);

        $name = !empty($validated['name']) ? $validated['name'] : ucfirst(explode('@', $validated['email'])[0]);
        $filerType = $validated['filer_type'] ?? 'fresh_filer';

        // Clean & format CNIC: 42101-1234567-1
        $cleaned = preg_replace('/[^0-9]/', '', $validated['cnic']);
        if (strlen($cleaned) !== 13) {
            throw ValidationException::withMessages([
                'cnic' => '13-digit Pakistani CNIC number darj karein (e.g. 42101-1234567-1).',
            ]);
        }
        $formattedCnic = substr($cleaned, 0, 5) . '-' . substr($cleaned, 5, 7) . '-' . substr($cleaned, 12, 1);
        $cnicHash = Client::generateCnicHash($formattedCnic);

        if (Client::where('cnic_hash', $cnicHash)->exists()) {
            throw ValidationException::withMessages([
                'cnic' => 'Yeh CNIC number pehle se registered hai. Barah-e-karam login karein.',
            ]);
        }

        // 1. Create User
        $user = User::create([
            'name' => $name,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'consultancy_id' => $consultancy->id,
            'role' => 'client',
            'phone' => $validated['phone'],
            'is_active' => true,
        ]);

        // 2. Create Client Profile
        $client = Client::create([
            'consultancy_id' => $consultancy->id,
            'user_id' => $user->id,
            'name' => $name,
            'cnic' => $formattedCnic,
            'cnic_hash' => $cnicHash,
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'status' => 'active',
        ]);

        // 3. Create initial Tax Return for Tax Year 2026-2027
        $taxYear = TaxYear::where('is_active', true)->first();
        if ($taxYear) {
            $taxReturn = TaxReturn::create([
                'consultancy_id' => $consultancy->id,
                'client_id' => $client->id,
                'tax_year_id' => $taxYear->id,
                'filing_type' => 'original',
                'selected_categories' => ['salary', 'advance_tax', 'expenses', 'assets', 'reconciliation'],
                'status' => 'draft',
                'fee_amount' => $consultancy->default_return_fee ?? 2500.00,
            ]);

            $taxReturn->wealthReconciliation()->create([
                'opening_wealth' => 0,
                'net_inflows' => 0,
                'living_expenses' => 0,
                'other_outflows' => 0,
                'closing_wealth' => 0,
                'unreconciled_difference' => 0,
            ]);
        }

        // 4. Log in immediately
        Auth::login($user);
        $request->session()->regenerate();

        if (isset($taxReturn)) {
            return redirect()->route('wizard.filing', $taxReturn)
                ->with('success', 'Mubarak ho! Aapka account ban gaya. Step-by-step return filing shuru karein!');
        }

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Aap kamiyabi se log out ho chuke hain.');
    }
}
