<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use Illuminate\Support\Facades\Hash;


class RegisterController extends Controller
{
    /**
     * Show the application registration form.
     *
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        return view('signup'); // Make sure you have a 'register.blade.php' view
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request)
    {
        // 1. Validate the incoming request data
        $request->validate([
            'firstname'  => ['required', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'lastname'   => ['required', 'string', 'max:255'],
            'store_name' => ['required', 'string', 'max:255', 'unique:owners'], // Store name should be unique
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:owners'], // Email must be unique in the 'owners' table
            'contact'    => ['nullable', 'string', 'max:20'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' requires a 'password_confirmation' field
        ]);

        // 2. Create a new Owner record
        $owner = Owner::create([
            'firstname'  => $request->firstname,
            'middlename' => $request->middlename,
            'lastname'   => $request->lastname,
            'store_name' => $request->store_name,
            'store_address'=> $request->store_address,
            'email'      => $request->email,
            'contact'    => $request->contact,
            'owner_pass' => Hash::make($request->password), // Hash the password using owner_pass column
            'status'     => 'Pending', // Default status: 'Pending' for Super Admin approval
            // 'status'     => 'Active', // Alternatively, set to 'Active' if no approval is needed
            'created_on' => now(), // Set the registration date
        ]);


        return redirect()->route('login')->with('success', 'Registration successful! Please log in.');
    }
}