<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SuperAdmin;
use App\Models\Subscription; // Ensure Subscription model is imported
use App\Models\Owner;
use App\Models\Staff;
use Illuminate\Validation\ValidationException; // Ensure this is imported

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('login'); // Shared login form
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $email = $request->email;
        $password = $request->password;

        // Try to find the user in any of the guards
        $authenticatedUser = null;
        $guardUsed = null;

        // 1. Attempt Super Admin login
        $superAdmin = SuperAdmin::where('email', $email)->first();
        if ($superAdmin && Hash::check($password, $superAdmin->super_pass)) {
            $authenticatedUser = $superAdmin;
            $guardUsed = 'super_admin';
        }

        // 2. Attempt Owner login (only if not already authenticated as Super Admin)
        if (!$authenticatedUser) {
            $owner = Owner::where('email', $email)->first();

            // Check if owner exists AND password matches
            if ($owner && Hash::check($password, $owner->owner_pass)) {

                // IMPORTANT: Check Owner's registration status first (e.g., if Super Admin approved them)
             if ($owner->status === 'Deactivated') {
                    return redirect()->back()->withInput($request->only('email'))
                        ->with('error', 'Your account has been deactivated. Please contact support.');
                }
                // Add other statuses like 'Rejected' if applicable

                // If status allows login, authenticate the owner (log them in)
                Auth::guard('owner')->login($owner);
                $authenticatedUser = $owner;
                $guardUsed = 'owner';

                // Now, *after successful login*, check for their active subscription using the relationship
                $activeSubscription = $owner->activeSubscription()->first();

                if ($activeSubscription && $owner->status === 'Active') {
                    return redirect()->intended(route('dashboards.owner.dashboard'));
                } else if ($activeSubscription && $owner->status === 'Pending') {
                    Auth::guard('owner')->logout();
                    return redirect()->route('login')->with('error', 'Your account is pending for approval. Please wait for activation.');
                } else {
                    return redirect()->route('subscription.selection')->with('info', 'It looks like you don\'t have an active subscription.');
                }
            }
        }
        // 3. Attempt Staff login (only if not already authenticated as Super Admin or Owner)
        if (!$authenticatedUser) {
            $staff = Staff::where('email', $email)->first();
            if ($staff) {
                // Check staff status BEFORE password
                if ($staff->status === 'Deactivated') {
                    throw ValidationException::withMessages([
                        'email' => 'Your account has been deactivated. Please contact support.',
                    ]);
                }
                // If staff is active, check password
                if (Hash::check($password, $staff->staff_pass)) {
                    $authenticatedUser = $staff;
                    $guardUsed = 'staff';
                }
            }
        }

        // --- Handle authentication result ---

        if ($authenticatedUser && $guardUsed) {
            // Authentication already happened for owner, so this block is for SuperAdmin and Staff
            // Or if owner was logged in but then redirected to subscription selection,
            // this block won't be reached for them.
            Auth::guard($guardUsed)->login($authenticatedUser); // Re-login if not already (e.g., for SuperAdmin/Staff)
            $request->session()->regenerate(); // Regenerate session on successful login for security

            // Redirect based on the authenticated user type
            switch ($guardUsed) {
                case 'super_admin':
                    return redirect()->route('clients.index');
                case 'owner':
                    // This case should ideally not be reached if the owner was already redirected
                    // based on subscription status. It's here as a fallback.
                    return redirect()->intended(route('dashboards.owner.dashboard'));
                case 'staff':
                    return redirect()->route('staff.dashboard');
            }
        }

        // If no user was found OR password didn't match for active user
        throw ValidationException::withMessages([
            'email' => 'Invalid email or password.',
        ]);
    }

    public function logout(Request $request)
    {
        // Log out from all guards (whichever is active)
        if (Auth::guard('super_admin')->check()) {
            Auth::guard('super_admin')->logout();
        } elseif (Auth::guard('owner')->check()) {
            Auth::guard('owner')->logout();
        } elseif (Auth::guard('staff')->check()) {
            Auth::guard('staff')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
