<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class Notifications extends Component
{
    public $active = false;       // Dropdown visibility
    public $notifs = [];          // Current notifications displayed
    public $count = 0;            // Unread notifications count
    public $filter = 'all';       // Current filter: 'all' or 'unread'

    public $showModal = false;
    public $notifTitle = '';
    public $notifMessage = '';
    public $notifDate = '';
    public $notifCountRead = null;
    public $notifID = null;

    public function mount() {
        $this->loadNotifs();
    }

    public function togglePanel() {
        $this->active = !$this->active;

        if ($this->active && $this->filter === 'all') {
            $this->markAsSeen();
        }
    }

    public function loadNotifs() {
        
        $user_email = Auth::guard('owner')->check() 
            ? Auth::guard('owner')->user()->email 
            : Auth::guard('staff')->user()->email;

        if ($this->filter === 'all') {

            $this->notifs = collect(DB::select("
                SELECT n.notif_id, n.notif_title, n.notif_message, n.notif_created_on, un.usernotif_is_read, n.notif_type
                FROM notification n
                JOIN user_notification un ON n.notif_id = un.notif_id
                WHERE un.usernotif_email = ?
                ORDER BY n.notif_created_on DESC
            ", [$user_email]));

        } elseif ($this->filter === 'unread') {

            $this->notifs = collect(DB::select("
                SELECT n.notif_id, n.notif_title, n.notif_message, n.notif_created_on, un.usernotif_is_read, n.notif_type
                FROM notification n
                JOIN user_notification un ON n.notif_id = un.notif_id
                WHERE un.usernotif_email = ?
                AND un.usernotif_is_read = 0
                ORDER BY n.notif_created_on DESC
            ", [$user_email]));
        }

        $this->count = collect(DB::select("
            SELECT COUNT(*) as cnt
            FROM user_notification
            WHERE usernotif_email = ?
            AND usernotif_seen = 0
        ", [$user_email]))->first()->cnt ?? 0;
    }

    public function showAll() {
        $this->filter = 'all';
        $this->loadNotifs();
    }

    public function showUnread() {
        $this->filter = 'unread';
        $this->loadNotifs();
    }

    public function markAsSeen() {
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

    public function refreshNotifs() {
        $this->loadNotifs();
    }

    public function openModal($id, $title, $message, $created) {

        $user_email = Auth::guard('owner')->check() 
            ? Auth::guard('owner')->user()->email 
            : Auth::guard('staff')->user()->email;

        DB::update('
            update user_notification
            set usernotif_is_read = 1, usernotif_read_at = ?
            where usernotif_email = ?
            and notif_id = ?
            ', [now(), $user_email, $id]
        );

        $countRead = collect(DB::select('
            select count(un.usernotif_is_read) as countRead,
            n.notif_id as notif
            from user_notification un
            join notification n 
            on un.notif_id = n.notif_id
            where un.usernotif_is_read = 1
            and n.notif_id = ?
            group by n.notif_id
        ', [$id]))->first();

        // $this->loadNotifs();

        $this->notifTitle = $title;
        $this->notifMessage = $message;
        $this->notifDate = $created;
        $this->notifCountRead = $countRead;
        $this->showModal = true;
    }

    public function closeModal() {
        $this->showModal = false;
    }

    public function render()
    {
        return view('livewire.notifications');
    }
}
