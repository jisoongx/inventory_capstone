<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// class Notifications extends Component
// {
//     public $notifs = [];
//     public $count = 0;
//     public $active = false;

//     public function render()
//     {
//         $this->loadNotifs();
//         return view('livewire.notifications');
//     }

//     public function loadNotifs()
//     {
//         $role = Auth::guard('owner')->check() ? 'owner' : 'staff';
//         $user_email = Auth::guard('owner')->check() 
//             ? Auth::guard('owner')->user()->email 
//             : Auth::user()->email;

//         $this->notifs = DB::select("
//             SELECT n.*, un.*
//             FROM notification n
//             JOIN user_notification un
//             ON n.notif_id = un.notif_id
//             WHERE un.usernotif_type IN ('all', ?)
//               AND un.usernotif_email = ?
//             ORDER BY n.notif_created_on DESC
//         ", [$role, $user_email]);

//         $result = DB::select("
//             SELECT COUNT(*) AS total
//             FROM user_notification
//             WHERE usernotif_type IN ('all', ?)
//               AND usernotif_email = ?
//               AND usernotif_seen = 0
//         ", [$role, $user_email]);

//         $this->count = $result[0]->total ?? 0;
//     }

//     public function togglePanel()
//     {
//         $this->active = !$this->active;
//         if ($this->active) {
//             $this->markAsSeen();
//         }
//     }

//     public function markAsSeen()
//     {
//         $role = Auth::guard('owner')->check() ? 'owner' : 'staff';
//         $user_email = Auth::guard('owner')->check() 
//             ? Auth::guard('owner')->user()->email 
//             : Auth::user()->email;

//         DB::update("
//             UPDATE user_notification
//             SET usernotif_seen = 1
//             WHERE usernotif_email = ?
//             AND usernotif_type IN ('all', ?)
//         ", [$user_email, $role]);

//         $this->loadNotifs(); 
//     }

//     public function showUnread()
//     {
//         $role = Auth::guard('owner')->check() ? 'owner' : 'staff';
//         $user_email = Auth::guard('owner')->check() 
//             ? Auth::guard('owner')->user()->email 
//             : Auth::user()->email;

//         $this->notifs = collect(DB::select("
//             select n.notif_title, n.notif_message, n.notif_created_on
//             from notification n
//             join user_notification un
//             on n.notif_id = un.notif_id
//             WHERE un.usernotif_email = ?
//             AND un.usernotif_type IN ('all', ?)
//             AND un.usernotif_is_read = 0
//             ORDER BY n.notif_created_on DESC
//         ", [$user_email, $role]));
//     }




// }


class Notifications extends Component
{
    public $active = false;       // Dropdown visibility
    public $notifs = [];          // Current notifications displayed
    public $count = 0;            // Unread notifications count
    public $filter = 'all';       // Current filter: 'all' or 'unread'

    public function mount()
    {
        $this->loadNotifs();
    }

    public function togglePanel()
    {
        $this->active = !$this->active;

        if ($this->active && $this->filter === 'all') {
            $this->markAsSeen();
        }
    }

    public function loadNotifs()
    {
        $role = Auth::guard('owner')->check() ? 'owner' : 'staff';
        $user_email = Auth::guard('owner')->check() 
            ? Auth::guard('owner')->user()->email 
            : Auth::guard('staff')->user()->email;

        if ($this->filter === 'all') {

            $this->notifs = collect(DB::select("
                SELECT n.notif_title, n.notif_message, n.notif_created_on, un.usernotif_is_read
                FROM notification n
                JOIN user_notification un ON n.notif_id = un.notif_id
                WHERE un.usernotif_email = ?
                AND un.usernotif_type IN ('all', ?)
                ORDER BY n.notif_created_on DESC
            ", [$user_email, $role]));

        } elseif ($this->filter === 'unread') {

            $this->notifs = collect(DB::select("
                SELECT n.notif_title, n.notif_message, n.notif_created_on, un.usernotif_is_read
                FROM notification n
                JOIN user_notification un ON n.notif_id = un.notif_id
                WHERE un.usernotif_email = ?
                AND un.usernotif_type IN ('all', ?)
                AND un.usernotif_is_read = 0
                ORDER BY n.notif_created_on DESC
            ", [$user_email, $role]));
        }

        $this->count = collect(DB::select("
            SELECT COUNT(*) as cnt
            FROM user_notification
            WHERE usernotif_email = ?
            AND usernotif_seen = 0
        ", [$user_email]))->first()->cnt ?? 0;
    }

    public function showAll()
    {
        $this->filter = 'all';
        $this->loadNotifs();
    }

    public function showUnread()
    {
        $this->filter = 'unread';
        $this->loadNotifs();
    }

    public function markAsSeen()
    {
        $role = Auth::guard('owner')->check() ? 'owner' : 'staff';
        $user_email = Auth::guard('owner')->check() 
            ? Auth::guard('owner')->user()->email 
            : Auth::guard('staff')->user()->email;

        DB::update("
            UPDATE user_notification
            SET usernotif_seen = 1
            WHERE usernotif_email = ?
        ", [$user_email]);

        $this->count = 0;
    }

    public function render()
    {
        return view('livewire.notifications');
    }
}
