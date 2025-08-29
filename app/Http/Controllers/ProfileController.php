<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException; 
use App\Models\Staff;
use App\Http\Controllers\ActivityLogController;



class ProfileController extends Controller
{

    public function showSuperAdminProfile()
    {
        $superAdmin = Auth::guard('super_admin')->user();
        return view('dashboards.super_admin.super_profile', compact('superAdmin'));
    }

    public function updateSuperAdminProfile(Request $request)
    {
        $superAdmin = Auth::guard('super_admin')->user(); 
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $superAdmin->getAuthPassword())) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $passwordColumn = $superAdmin->getAuthPasswordName();
        $superAdmin->$passwordColumn = Hash::make($request->password);
        $superAdmin->save();

        $user = Auth::guard('super_admin')->user();
        ActivityLogController::log('Password Changed', 'super_admin', $user, $request->ip());
        return redirect()->route('super_admin.profile')->with('success', 'Password updated successfully!');
    }

    public function showOwnerProfile()
    {
        $owner = Auth::guard('owner')->user();
        return view('dashboards.owner.owner_profile', compact('owner'));
    }
 
    public function updateOwnerProfile(Request $request)
    {
        $owner = Auth::guard('owner')->user();

        $validationRules = [
            'store_name' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:20'],
            'store_address' => ['nullable', 'string', 'max:255'],
        ];

        if ($request->filled('current_password') || $request->filled('password') || $request->filled('password_confirmation')) {
            $validationRules['current_password'] = ['required', 'string'];
            $validationRules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $request->validate($validationRules);

        $profileUpdated = false;
        $passwordChanged = false;

        if ($request->filled('store_name') && $request->store_name !== $owner->store_name) {
            $owner->store_name = $request->store_name;
            $profileUpdated = true;
        }

        if ($request->filled('contact') && $request->contact !== $owner->contact) {
            $owner->contact = $request->contact;
            $profileUpdated = true;
        }

        if ($request->filled('store_address') && $request->store_address !== $owner->store_address) {
            $owner->store_address = $request->store_address;
            $profileUpdated = true;
        }

        if ($request->filled('current_password')) {
            if (!Hash::check($request->current_password, $owner->owner_pass)) {
                throw ValidationException::withMessages([
                    'current_password' => ['The provided password does not match your current password.'],
                ]);
            }

            $owner->owner_pass = Hash::make($request->password);
            $passwordChanged = true;
        }

        $owner->save();

        if ($profileUpdated) {
            ActivityLogController::log(
                'Profile Updated',
                'owner',
                $owner,
                $request->ip()
            );
        }

        if ($passwordChanged) {
            ActivityLogController::log(
                'Password Changed',
                'owner',
                $owner,
                $request->ip()
            );
        }

        return redirect()->route('owner.profile')->with('success', 'Profile updated successfully!');
    }

    public function showStaffProfile()
    {
        $staffId = Auth::guard('staff')->id();
        $staff = Staff::with('owner')->find($staffId);

        if (!$staff) {
            Auth::guard('staff')->logout(); 
            return redirect()->route('login')->withErrors(['error' => 'Your staff account could not be found. Please log in again.']);
        }

        return view('dashboards.staff.profile', compact('staff'));
    }
   
    public function updateStaffProfile(Request $request)
    {
        $staff = Auth::guard('staff')->user(); 
      
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($request->current_password, $staff->getAuthPassword())) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $passwordColumn = $staff->getAuthPasswordName();
        $staff->$passwordColumn = Hash::make($request->password);
        $staff->save();

        ActivityLogController::log(
            'Staff password changed',
            'staff',
            $staff,
            $request->ip()
        );

        return redirect()->route('staff.profile')->with('success', 'Password updated successfully!');
    }
}