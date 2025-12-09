<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActLog;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class ActivityLogController extends Controller
{
    public function index()
    {
        $logs = [];
        $view = '';

        if (Auth::guard('super_admin')->check()) {
            
            if (!Auth::guard('super_admin')->check()) {
                abort(403, 'Unauthorized access.');
            }

            $superAdmin = Auth::guard('super_admin')->user();
            $logs = ActLog::with('superAdmin')
                ->where('super_id', $superAdmin->super_id)
                ->orderByDesc('log_timestamp')
                ->get();

            $view = 'dashboards.super_admin.actLogs';

        } elseif (Auth::guard('owner')->check()) {

            if (!Auth::guard('owner')->check()) {
                abort(403, 'Unauthorized access.');
            }

            $owner = Auth::guard('owner')->user();
            $logs = ActLog::with('owner')
                ->where('owner_id', $owner->owner_id)
                ->orderByDesc('log_timestamp')
                ->get();

            $view = 'dashboards.owner.actLogs';
        }

        return view($view, compact('logs'));
    }


    public function staffLogs()
    {
        $owner = Auth::guard('owner')->user();

        if (!Auth::guard('owner')->check()) {
            abort(403, 'Unauthorized access.');
        }

        $logs = ActLog::with('staff')
            ->whereHas('staff', function ($query) use ($owner) {
                $query->where('owner_id', $owner->owner_id);
            })
            ->orderByDesc('log_timestamp')
            ->get();

        return view('dashboards.staff.staffLogs', compact('logs'));
    }

    public function activity_search(Request $request)
    {
        $query = $request->input('query');
        $date  = $request->input('date');
        $time  = $request->input('time');
        $type  = $request->input('type');

        $logs = ActLog::query();

        if (Auth::guard('super_admin')->check()) {
            $user = Auth::guard('super_admin')->user();
            $logs->with('superAdmin')
                ->where('super_id', $user->super_id);
        } elseif (Auth::guard('owner')->check()) {
            $user = Auth::guard('owner')->user();

            if ($type === 'staff') {
                $logs->with('staff')
                    ->whereHas('staff', function ($q) use ($user) {
                        $q->where('owner_id', $user->owner_id);
                    });
            } else {
                $logs->with('owner')
                    ->where('owner_id', $user->owner_id)
                    ->whereNull('staff_id'); // exclude logs linked to staff

            }
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Search filter
        if ($query) {
            $logs->where(function ($q) use ($query) {
                $q->where('log_type', 'like', "%{$query}%")
                    ->orWhere('log_location', 'like', "%{$query}%");
            });
        }

        // Date filter
        if ($date) {
            $logs->whereDate('log_timestamp', $date);
        }

        // Time filter
        if ($time) {
            try {
                $logs->whereRaw("DATE_FORMAT(log_timestamp, '%H:%i') = ?", [$time]);
            } catch (\Exception $e) {
                return response()->json(['message' => 'Invalid time format.'], 400);
            }
        }

        return response()->json($logs->orderByDesc('log_timestamp')->get());
    }

    public static function log($type, $guard, $user, $ip)
    {
        $location = 'Unknown';
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            $location = 'Localhost (Development Machine)';
        } else {
            try {
                $res = Http::get("http://ip-api.com/json/{$ip}?fields=city,regionName,country");
                if ($res->successful()) {
                    $data = $res->json();
                    $location = "{$data['city']}, {$data['regionName']}, {$data['country']}";
                }
            } catch (\Exception $e) {
                $location = 'Lookup failed';
            }
        }

        ActLog::create([
            'log_type'     => $type,
            'super_id'     => $guard === 'super_admin' ? $user->super_id : null,
            'owner_id'     => $guard === 'owner' ? $user->owner_id : ($guard === 'staff' ? $user->owner_id : null),
            'staff_id'     => $guard === 'staff' ? $user->staff_id : null,
            'log_location' => $location,
            'log_timestamp' => Carbon::now(),
        ]);
    }
}
