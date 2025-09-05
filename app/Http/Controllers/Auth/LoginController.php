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

        $email = $request->email;
        $password = $request->password;
        $authenticatedUser = null;
        $guardUsed = null;

        // Super Admin
        $superAdmin = SuperAdmin::where('email', $email)->first();
        if ($superAdmin && Hash::check($password, $superAdmin->super_pass)) {
            $authenticatedUser = $superAdmin;
            $guardUsed = 'super_admin';
        }

        // Owner
        if (!$authenticatedUser) {
            $owner = Owner::where('email', $email)->first();
            if ($owner && Hash::check($password, $owner->owner_pass)) {
                Auth::guard('owner')->login($owner);
                $authenticatedUser = $owner;
                $guardUsed = 'owner';
            }
        }

        // Staff
        if (!$authenticatedUser) {
            $staff = Staff::where('email', $email)->first();
            if ($staff) {
                if ($staff->status === 'Deactivated') {
                    throw ValidationException::withMessages([
                        'email' => 'Your account has been deactivated.',
                    ]);
                }
                if (Hash::check($password, $staff->staff_pass)) {
                    $authenticatedUser = $staff;
                    $guardUsed = 'staff';
                }
            }
        }

        if ($authenticatedUser && $guardUsed) {
            Auth::guard($guardUsed)->login($authenticatedUser);
            $request->session()->regenerate();

            ActivityLogController::log('Login', $guardUsed, $authenticatedUser, $request->ip());
            switch ($guardUsed) {
                case 'super_admin':
                    return redirect()->route('subscription');
                    break;
                case 'owner':
                    $owner = $authenticatedUser;
                    $subscribe = $owner->subscription()->first();

                    if (!$subscribe) {
                        return redirect()->route('subscription.selection')
                            ->with('info', 'No active subscription.');
                    }
                    elseif ($owner->status === 'Active') {
                        $subscription = $owner->subscription;
                        if ($subscription->progress_view = 0  ) {
                            $subscription->progress_view = true;
                            $subscription->save();
                            return redirect()->route('subscription.progress');
                        }
                        return redirect()->route('dashboards.owner.dashboard');
                    }  elseif ($owner->status === 'Deactivated') {
                        return redirect()->route('subscription.expired')
                            ->with('info', 'Your subscription has expired. Please renew to continue enjoying our services.');
                    } 
                    break;

                case 'staff':
                    return redirect()->route('staff.dashboard');
                    break;
            }
        }

        throw ValidationException::withMessages([
            'email' => 'Invalid email or password.',
        ]);
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
