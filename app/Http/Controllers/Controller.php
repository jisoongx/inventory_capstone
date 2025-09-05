<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    //  protected function getNotifs() {
    //     $notifs = collect();

    //     if (Auth::guard('owner')->check()) {
    //         $account_id = Auth::guard('owner')->user()->owner_id;
    //         $role = 'owner';

    //     } elseif (Auth::guard('staff')->check()) {
    //         $account_id = Auth::guard('staff')->user()->staff_id;
    //         $role = 'staff';
            
    //     } else {
    //         return $notifs;
    //     }

    //     return collect(DB::select(
    //         "select n.*, un.*
    //          from notification n
    //          join user_notification un on n.notif_id = un.notif_id
    //          where un.usernotif_type in ('all', ?)
    //            and un.account_id = ?
    //          order by n.notif_created_on desc",
    //         [$role, $account_id]
    //     ));
    // }

    // protected function countNotifs() {
    //     $countNotifs = collect();

    //     if (Auth::guard('owner')->check()) {
    //         $account_id = Auth::guard('owner')->user()->owner_id;
    //         $role = 'owner';

    //     } elseif (Auth::guard('staff')->check()) {
    //         $account_id = Auth::guard('staff')->user()->staff_id;
    //         $role = 'staff';
            
    //     } else {
    //         return $countNotifs;
    //     }

    //     $result = DB::select("
    //         SELECT COUNT(*) AS total
    //         FROM user_notification
    //         WHERE usernotif_type IN ('all', ?)
    //         AND account_id = ?
    //         AND usernotif_seen = 0
    //     ", [$role, $account_id]);

    //     return $result[0]->total ?? 0;
    // }
}