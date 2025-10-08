<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use Illuminate\Support\Facades\Hash;

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
            'contact'    => ['nullable', 'digits:11', 'starts_with:09'],
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

        return redirect()->route('login')->with('success', 'Registration successful! Please log in.');
    }
}
