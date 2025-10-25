<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\SuperAdmin;
use App\Models\Owner;
use App\Models\Staff;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\ActivityLogController;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        // Try Super Admin
        if (Auth::guard('super_admin')->attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            ActivityLogController::log('Login', 'super_admin', Auth::guard('super_admin')->user(), $request->ip());
            return redirect()->route('subscription');
        }

        // Try Owner
        if (Auth::guard('owner')->attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            $owner = Auth::guard('owner')->user();

            if (is_null($owner->email_verified_at)) {
                Auth::guard('owner')->logout();

                // Store email in session for resend
                session(['unverified_email' => $request->email]);
                
                return back()->withErrors([
                    'email' => 'Please verify your email to continue.'
                ])->withInput($request->only('email'));
            }

            ActivityLogController::log('Login', 'owner', $owner, $request->ip());

            $latestSub = $owner->latestSubscription;

            // Case 1: Owner has NO subscription at all.
            if (!$latestSub) {
                return redirect()->route('subscription.selection')
                    ->with('info', 'Welcome! Please choose a plan to get started.');
            }

            // Case 2: Owner's latest subscription is ACTIVE.
            if ($latestSub->status === 'active') {
                return redirect()->route('dashboards.owner.dashboard');
            }

            // Case 3: Owner's latest subscription is EXPIRED.
            // As requested, expired users are directed to the dashboard.
            if ($latestSub->status === 'expired') {
                return redirect()->route('dashboards.owner.dashboard');
            }

            // Default Case: For any other status (e.g., 'pending', 'cancelled').
            return redirect()->route('subscription.selection')
                ->with('info', 'Please complete your subscription process.');
        }

        // Try Staff
        if (Auth::guard('staff')->attempt($request->only('email', 'password'))) {
            $request->session()->regenerate();
            $staff = Auth::guard('staff')->user();

            if ($staff->status === 'Deactivated') {
                Auth::guard('staff')->logout();
                return back()->withErrors([
                    'email' => 'Your account has been deactivated.',
                ])->withInput($request->only('email'));
            }

            ActivityLogController::log('Login', 'staff', $staff, $request->ip());
            return redirect()->route('staff.dashboard');
        }

        // If nothing matched, return with an error.
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }



    public function logout(Request $request)
    {
        $guard = null;
        $user = null;

        if (Auth::guard('super_admin')->check()) {
            $guard = 'super_admin';
            $user = Auth::guard('super_admin')->user();
            Auth::guard('super_admin')->logout();
        } elseif (Auth::guard('owner')->check()) {
            $guard = 'owner';
            $user = Auth::guard('owner')->user();
            Auth::guard('owner')->logout();
        } elseif (Auth::guard('staff')->check()) {
            $guard = 'staff';
            $user = Auth::guard('staff')->user();
            Auth::guard('staff')->logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user && $guard) {
            ActivityLogController::log('Logout', $guard, $user, $request->ip());
        }

        return redirect()->route('login');
    }
}
