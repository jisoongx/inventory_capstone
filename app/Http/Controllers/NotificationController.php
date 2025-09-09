<?php 

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class NotificationController extends Controller
{ 
    public function index() {

        $notification = collect(DB::select('
            select notif_title, notif_message, notif_created_on, notif_target, notif_type
            from notification
            order by notif_created_on desc;
        '));
        
        return view('dashboards.super_admin.notification', [
            'notification' => $notification,
        ]);
        
    }

    public function send_notification(Request $request) {
        if (Auth::guard('super_admin')->check()) {
            $super_admin = Auth::guard('super_admin')->user();
            $super_id = $super_admin->super_id;

            $request->validate([
                'message' => 'required|string|max:1000',
                'title' => 'required|string|max:70',
                'recipients' => 'required|string|max:20',
            ]);

            DB::insert("
                INSERT INTO notification (notif_title, notif_message, notif_target, notif_created_on, super_id, notif_type)
                VALUES (?, ?, ?, NOW(), ?, 'system')
            ", [$request->title, $request->message, $request->recipients, $super_id]);

            $notif_id = DB::getPdo()->lastInsertId();

            
            if ($request->recipients === 'owner') {
                $users = DB::select("SELECT email AS user_email FROM owners");
                $type = 'owner';

            } elseif ($request->recipients === 'staff') {
                $users = DB::select("SELECT email AS user_email FROM staff");
                $type = 'staff';

            } else {
                $owners = DB::select("SELECT email AS user_email FROM owners");
                $staff = DB::select("SELECT email AS user_email FROM staff");

                $users = array_merge($owners, $staff);
                $type = 'all';
            }

            foreach ($users as $user) {
                DB::insert("
                    INSERT INTO user_notification (usernotif_type, usernotif_email, usernotif_is_read, usernotif_read_at, notif_id)
                    VALUES (?, ?, 0, NULL, ?)
                ", [$type, $user->user_email, $notif_id]);
            }

            return redirect()->route('dashboards.super_admin.notification')->with('success', 'Notification sent successfully.');
        }
    }

// public function markSeen(Request $request)
// {
//     if (Auth::guard('owner')->check()) {
//         $account_id = Auth::guard('owner')->user()->owner_id;
//         $role = 'owner';
//     } elseif (Auth::guard('staff')->check()) {
//         $account_id = Auth::guard('staff')->user()->staff_id;
//         $role = 'staff';
//     } else {
//         return response()->json(['error' => 'Unauthorized'], 401);
//     }

//     DB::table('user_notification')
//         ->where('account_id', $account_id)
//         ->where('usernotif_type', $role)
//         ->where('usernotif_seen', 0)
//         ->update([
//             'usernotif_seen' => 1,
//             'usernotif_seen_at' => now()
//         ]);

//     return response()->json(['success' => true]);
// }




}



?>
