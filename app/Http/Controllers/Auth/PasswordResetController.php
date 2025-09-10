<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    // Map brokers to their custom password columns
    protected $passwordColumns = [
        'super_admins' => 'super_pass',
        'owners'       => 'owner_pass',
        'staff'        => 'staff_pass',
    ];

    /**
     * Send password reset link email.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $found = false;

        foreach (['super_admins', 'owners', 'staff'] as $broker) {
            $status = Password::broker($broker)->sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                $found = true;
                break; // Stop at the first broker that matches
            }
        }

        if ($found) {
            return back()->with('status', 'A password reset link has been sent to your email!');
        }

        return back()->withErrors(['email' => 'We couldn’t find an account with that email.']);
    }

    /**
     * Handle password reset.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        foreach (['super_admins', 'owners', 'staff'] as $broker) {
            $user = Password::broker($broker)->getUser($request->only('email'));

            if ($user) {
                $passwordColumn = $this->passwordColumns[$broker];

                $status = Password::broker($broker)->reset(
                    $request->only('email', 'password', 'password_confirmation', 'token'),
                    function ($user, $password) use ($passwordColumn) {
                        // Update only the custom password column
                        $user->{$passwordColumn} = Hash::make($password);
                        $user->setRememberToken(Str::random(60)); // optional if you want "remember me"
                        $user->save();
                    }
                );

                if ($status === Password::PASSWORD_RESET) {
                    return redirect()->route('login')->with('success', 'Password reset successfully! You can now log in.');
                } else {
                    return back()->withErrors(['email' => [__($status)]]);
                }
            }
        }

        return back()->withErrors(['email' => 'Invalid reset token or email.']);
    }
}
