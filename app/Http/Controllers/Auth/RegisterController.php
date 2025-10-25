<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'firstname'  => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-]+$/'],
            'middlename' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-]+$/'],
            'lastname'   => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-]+$/'],
            'store_name' => ['required', 'string', 'max:255', 'unique:owners,store_name'],
            'email'      => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Check if email already exists in owners or staff
                    $existsInOwners = DB::table('owners')->where('email', $value)->exists();
                    $existsInStaff  = DB::table('staff')->where('email', $value)->exists();

                    if ($existsInOwners || $existsInStaff) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                },
            ],
            'contact' => [
                'nullable',
                'digits:11',
                'starts_with:09',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $existsInOwners = DB::table('owners')->where('contact', $value)->exists();
                        $existsInStaff  = DB::table('staff')->where('contact', $value)->exists();

                        if ($existsInOwners || $existsInStaff) {
                            $fail('The ' . $attribute . ' number has already been taken.');
                        }
                    }
                },
            ],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ], [

            'firstname.regex' => 'First name must only contain letters.',
            'middlename.regex' => 'Middle name must only contain letters.',
            'lastname.regex' => 'Last name must only contain letters.',
            'store_name.unique' => 'This store name is already registered.',
            'email.unique' => 'This email is already taken.',
            'contact.digits' => 'Contact number must be exactly 11 digits.',
            'contact.starts_with' => 'The contact number must start with 09.',
            'password.min'       => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password does not match.',
        ]);

        $owner = Owner::create([
            'firstname'     => strtoupper($request->firstname),
            'middlename'    => strtoupper($request->middlename),
            'lastname'      => strtoupper($request->lastname),
            'store_name'    => strtoupper($request->store_name),
            'store_address' => $request->store_address,
            'email'         => $request->email,
            'contact'       => $request->contact,
            'owner_pass'    => Hash::make($request->password),
            'created_on'    => now(),
        ]);

        // 🧩 3. Generate verification token
        $owner->generateVerificationToken();

        // 🧩 4. Send verification email
        try {
            Mail::to($owner->email)->send(new VerifyEmail($owner));
        } catch (\Exception $e) {
            // Optional: log if email fails
            \Log::error('Email verification failed: ' . $e->getMessage());
        }

        // 🧩 5. Redirect user with success message
        return redirect()
            ->route('login')
            ->with('success', 'Registration successful! Please check your email to verify your account.');
    }


    public function verifyEmail($token)
    {
        $owner = Owner::where('verification_token', $token)->first();

        if (!$owner) {
            return redirect()->route('login')->with('error', 'Invalid or expired verification link.');
        }

        $owner->markEmailAsVerified();

        return redirect()->route('login')->with('success', 'Email verified successfully! You may now log in.');
    }

    public function resendVerification()
    {
        $email = session('unverified_email');

        if (!$email) {
            return redirect()->route('login')->with('error', 'Email not found in session.');
        }

        $owner = Owner::where('email', $email)->first();

        if (!$owner) {
            return redirect()->route('login')->with('error', 'Email not found.');
        }

        if ($owner->hasVerifiedEmail()) {
            return redirect()->route('login')->with('info', 'Your email is already verified.');
        }

        // Generate new token if missing
        if (!$owner->verification_token) {
            $owner->generateVerificationToken();
        }

        Mail::to($owner->email)->send(new VerifyEmail($owner));

        return redirect()->route('login')->with('success', 'Verification email resent successfully! Please check your inbox.');
    }
}
