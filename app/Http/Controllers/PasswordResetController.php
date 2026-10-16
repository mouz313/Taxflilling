<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password request form.
     */
    public function showForgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.forgot-password');
    }

    /**
     * Send password reset link to user's email.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string'],
        ], [
            'email.required' => 'Barah-e-karam apna email address ya phone number darj karein.',
        ]);

        $input = trim($request->input('email'));
        $email = $input;

        // If user entered phone number instead of email, resolve their email
        if (! filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $userByPhone = User::where('phone', $input)->first();
            if ($userByPhone) {
                $email = $userByPhone->email;
            }
        }

        // Send reset link using Laravel's password broker
        $status = Password::broker()->sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Humne aapke email par password reset link bhej diya hai. Barah-e-karam apna inbox (aur spam folder) check karein.');
        }

        // Even if user not found, show neutral confirmation to prevent user enumeration
        return back()->with('status', 'Agar aapka account registered hai, to reset link aapke email par bhej diya gaya hai.');
    }

    /**
     * Show the reset password form with the token.
     */
    public function showResetPassword(Request $request, string $token)
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', PasswordRule::min(8), 'confirmed'],
        ], [
            'token.required' => 'Reset token ghalat ya missing hai.',
            'email.required' => 'Email address lazmi hai.',
            'password.required' => 'Naya password darj karein.',
            'password.min' => 'Password kam az kam 8 characters ka hona chahiye.',
            'password.confirmed' => 'Confirm password match nahi kar raha.',
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('success', 'Aapka password kamiyabi se update ho gaya hai. Ab aap naye password se login kar sakte hain.');
        }

        throw ValidationException::withMessages([
            'email' => ['Yeh password reset link expire ho chuka hai ya invalid hai. Barah-e-karam naya link request karein.'],
        ]);
    }
}
