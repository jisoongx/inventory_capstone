<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Don't forget to import Auth facade
use Illuminate\Support\Facades\Hash; // Needed for password hashing
use Illuminate\Validation\ValidationException; // For custom validation error handling
use App\Models\Staff; // Make sure to import the Staff model


class ProfileController extends Controller
{
    /**
     * Display the Super Admin's profile.
     * Accessible via Route::get('super-admin/profile', [ProfileController::class, 'showSuperAdminProfile'])->name('super_admin.profile');
     *
     * @return \Illuminate\View\View
     */
    public function showSuperAdminProfile()
    {
        $superAdmin = Auth::guard('super_admin')->user();
        return view('dashboards.super_admin.profile', compact('superAdmin'));
    }

    /**
     * Handle the update for Super Admin's profile (specifically password).
     * Accessible via Route::put('/super-admin/profile', [ProfileController::class, 'updateSuperAdminProfile'])->name('super_admin.profile.update');
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSuperAdminProfile(Request $request)
    {
        $superAdmin = Auth::guard('super_admin')->user(); // Get the currently authenticated super admin

        // Validate the incoming request data for password change
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Check if the provided current password matches the stored hashed password
        if (!Hash::check($request->current_password, $superAdmin->getAuthPassword())) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        // If current password matches, hash and update the new password
        $passwordColumn = $superAdmin->getAuthPasswordName();
        $superAdmin->$passwordColumn = Hash::make($request->password);
        $superAdmin->save();

        // Redirect back to the profile page with a success message
        return redirect()->route('super_admin.profile')->with('success', 'Password updated successfully!');
    }

    /**
     * Display the Owner's profile.
     * Accessible via Route::get('owner/profile', [ProfileController::class, 'showOwnerProfile'])->name('owner.profile');
     *
     * @return \Illuminate\View\View
     */
    public function showOwnerProfile()
    {
        $owner = Auth::guard('owner')->user();
        return view('dashboards.owner.profile', compact('owner'));
    }

    /**
     * Handle the update for Owner's profile (specifically password).
     * Accessible via Route::put('/owner/profile', [ProfileController::class, 'updateOwnerProfile'])->name('owner.profile.update');
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateOwnerProfile(Request $request) // Renamed from updateOwnerDetails/updateOwnerPassword
    {
        $owner = Auth::guard('owner')->user();

        // Validate general profile fields (can be nullable if not always submitted)
        $validationRules = [
            'store_name' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:20'],
            'store_address' => ['nullable', 'string', 'max:255'],
        ];

        // Conditionally add password validation rules if password fields are present
        if ($request->filled('current_password') || $request->filled('password') || $request->filled('password_confirmation')) {
            $validationRules['current_password'] = ['required', 'string'];
            $validationRules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $request->validate($validationRules);

        // Update general profile attributes if they are present in the request
        if ($request->filled('store_name')) {
            $owner->store_name = $request->store_name;
        }
        if ($request->filled('contact')) {
            $owner->contact = $request->contact;
        }
        if ($request->filled('store_address')) {
            $owner->store_address = $request->store_address;
        }

        // Handle password update if password fields are present
        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $owner->getAuthPassword())) {
                throw ValidationException::withMessages([
                    'current_password' => ['The provided password does not match your current password.'],
                ]);
            }
            $passwordColumn = $owner->getAuthPasswordName();
            $owner->$passwordColumn = Hash::make($request->password);
        }

        $owner->save();

        return redirect()->route('owner.profile')->with('success', 'Profile updated successfully!');
    }



    /**
     * Display the Staff's profile.
     * Accessible via Route::get('staff/profile', [ProfileController::class, 'showStaffProfile'])->name('staff.profile');
     *
     * @return \Illuminate\View\View
     */
    public function showStaffProfile()
    {
        // Get the ID of the currently authenticated staff member
        $staffId = Auth::guard('staff')->id();

        // Fetch the staff member with the 'owner' relationship eager loaded
        // This prevents an N+1 query problem and ensures 'owner' data is available
        $staff = Staff::with('owner')->find($staffId);

        // If for some reason the staff member isn't found (e.g., deleted), redirect or show error
        if (!$staff) {
            Auth::guard('staff')->logout(); // Log out the invalid user
            return redirect()->route('login')->withErrors(['error' => 'Your staff account could not be found. Please log in again.']);
        }

        return view('dashboards.staff.profile', compact('staff'));
    }

    /**
     * Handle the update for Staff's profile (specifically password).
     * Accessible via Route::put('/staff/profile', [ProfileController::class, 'updateStaffProfile'])->name('staff.profile.update');
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStaffProfile(Request $request)
    {
        $staff = Auth::guard('staff')->user(); // Get the currently authenticated staff member

        // Validate the incoming request data for password change
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Check if the provided current password matches the stored hashed password
        if (!Hash::check($request->current_password, $staff->getAuthPassword())) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        // If current password matches, hash and update the new password
        $passwordColumn = $staff->getAuthPasswordName();
        $staff->$passwordColumn = Hash::make($request->password);
        $staff->save();

        // Redirect back to the profile page with a success message
        return redirect()->route('staff.profile')->with('success', 'Password updated successfully!');
    }
}
