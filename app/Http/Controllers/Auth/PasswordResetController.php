<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Owner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;


class PasswordResetController extends Controller
{
    public function showRequestForm()
{
    return view('forgot-password');
}


    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $owner = Owner::where('email', $request->email)->first();

        if (!$owner) {
            return back()->withErrors(['email' => 'No account found with that email.']);
        }

        $token = $owner->generatePasswordResetToken();

        $resetUrl = url("/reset-password/{$token}?email=" . urlencode($owner->email));

        // You can make a custom Mailable later, but let's use a simple example:
        Mail::send('emails.password-reset', ['owner' => $owner, 'resetUrl' => $resetUrl], function ($message) use ($owner) {
            $message->to($owner->email)
                ->subject('Reset Your ShopLytix Password');
        });


        return back()->with('status', 'Password reset link sent to your email!');
    }

    public function showResetForm($token, Request $request)
    {
        $email = $request->query('email');

        return view('reset-password', ['token' => $token, 'email' => $email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $owner = Owner::where('email', $request->email)
            ->where('reset_token', $request->token)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$owner) {
            return back()->withErrors(['email' => 'Invalid or expired reset link.']);
        }

        $owner->owner_pass = Hash::make($request->password);
        $owner->clearPasswordResetToken();
        $owner->save();

        return redirect()->route('login')->with('success', 'Password reset successful! You can now log in.');
    }
}
