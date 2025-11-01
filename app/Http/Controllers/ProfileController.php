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

        // Get the most recent subscription based on the farthest 'subscription_end' date
        $subscription = $owner->subscription()
            ->with('planDetails')
            ->orderByDesc('subscription_end')  // Sort by the latest subscription end date
            ->first();  // Fetch the most recent (latest) subscription

        return view('dashboards.owner.owner_profile', compact('owner', 'subscription'));
    }



    public function updateOwnerProfile(Request $request)
    {
        $owner = Auth::guard('owner')->user();
        $validationRules = [
            'store_name' => ['nullable', 'string', 'max:255'],
            'contact' => [
                'nullable',
                'regex:/^09\d{9}$/', 
                'min:11',
            ],
            'store_address' => ['nullable', 'string', 'max:255'],
        ];


        if ($request->filled('current_password') || $request->filled('password') || $request->filled('password_confirmation')) {
            $validationRules['current_password'] = ['required', 'string'];
            $validationRules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        try {
            $request->validate($validationRules);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json(['errors' => $e->errors()], 422);
            }
            throw $e; 
        }

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
                return response()->json([
                    'errors' => ['current_password' => ['The provided password does not match your current password.']]
                ], 422);
            }

            $owner->owner_pass = Hash::make($request->password);
            $passwordChanged = true;
        }

        $owner->save();
        $owner->refresh();

        if ($request->ajax()) {
            if ($profileUpdated && $passwordChanged) {
                return response()->json([
                    'success' => 'Profile and password updated successfully!',
                    'owner'   => $owner
                ]);
            } elseif ($profileUpdated) {
                return response()->json([
                    'success' => 'Profile updated successfully!',
                    'owner'   => $owner
                ]);
            } elseif ($passwordChanged) {
                return response()->json([
                    'success' => 'Password updated successfully!',
                    'owner'   => $owner
                ]);
            } else {
                return response()->json([
                    'info'  => 'No changes were made.',
                    'owner' => $owner
                ]);
            }
        }

        if ($profileUpdated && $passwordChanged) {
            return redirect()->back()->with('success', 'Profile and password updated successfully!');
        } elseif ($profileUpdated) {
            return redirect()->back()->with('success', 'Profile updated successfully!');
        } elseif ($passwordChanged) {
            return redirect()->back()->with('success', 'Password updated successfully!');
        } else {
            return redirect()->back()->with('info', 'No changes were made.');
        }
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

    
        if ($request->filled('current_password') || $request->filled('password')) {
            $request->validate([
                'current_password' => ['required', 'string'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            if (!Hash::check($request->current_password, $staff->getAuthPassword())) {
                $errors = ['current_password' => ['The provided password does not match your current password.']];

                if ($request->ajax()) {
                    return response()->json(['errors' => $errors], 422);
                }

                throw ValidationException::withMessages($errors);
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

            if ($request->ajax()) {
                return response()->json(['success' => 'Password updated successfully!']);
            }

            return redirect()->route('staff.profile')->with('success', 'Password updated successfully!');
        }

        
        if ($request->filled('contact')) {
            $request->validate([
                'contact' => [
                    'bail',
                    'required',
                    'regex:/^09/', 
                    'digits:11', 
                ],
            ], [
                'contact.required' => 'Please enter your contact number.',
                'contact.regex' => 'The contact number must start with 09.',
                'contact.digits' => 'The contact number must be exactly 11 digits.',
            ]);


            $staff->contact = $request->contact;
            $staff->save();

            ActivityLogController::log(
                'Staff contact updated',
                'staff',
                $staff,
                $request->ip()
            );

            if ($request->ajax()) {
                return response()->json([
                    'success' => 'Profile updated successfully!',
                    'staff' => $staff,
                ]);
            }

            return redirect()->route('staff.profile')->with('success', 'Profile updated successfully!');
        }

        if ($request->ajax()) {
            return response()->json(['info' => 'No changes detected.']);
        }

        return redirect()->route('staff.profile')->with('info', 'No changes detected.');
    }
}
