<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TechnicalRequest extends Component
{
    public $requests = null;
    public $countUnread = null;
    public $convos = [];
    public $recentreq = null;

    public $newMessage = '';

    public $currentReqId = null;

    public $addModalOpen = false;
    public $addRequestTitle = '';
    public $addRequestMessage = '';

    public function mount() {
        $this->loadRequests();
        // $this->refreshConvo(); 
    }

    private function loadRequests() {
        if (Auth::guard('owner')->check()) {
            $owner_id = Auth::guard('owner')->user()->owner_id;
            $this->requests = collect(DB::select("
                SELECT tr.*, cm_max.last_message_id, cm_max.last_message_date
                FROM technical_request tr
                JOIN (
                    SELECT req_id, MAX(msg_id) AS last_message_id, MAX(msg_date) as last_message_date
                    FROM conversation_message
                    GROUP BY req_id
                ) cm_max ON tr.req_id = cm_max.req_id
                WHERE tr.owner_id = ?
                ORDER BY cm_max.last_message_id DESC
            ", [$owner_id]));

            $this->countUnread = collect(DB::select("
                select cm.req_id, count(cm.req_id) as unread_count
                from conversation_message cm
                join technical_request tr on cm.req_id = tr.req_id
                join owners o on tr.owner_id = o.owner_id
                where cm.msg_seen_at is null
                and cm.sender_type = 'super'
                and o.owner_id = ?
                group by cm.req_id
            ", [$owner_id]))->pluck('unread_count', 'req_id');

        }

        if (Auth::guard('staff')->check()) {
            $staff_id = Auth::guard('staff')->user()->staff_id;
            $this->requests = collect(DB::select("
                SELECT tr.*, cm_max.last_message_id, cm_max.last_message_date
                FROM technical_request tr
                JOIN (
                    SELECT req_id, MAX(msg_id) AS last_message_id, MAX(msg_date) as last_message_date
                    FROM conversation_message
                    GROUP BY req_id
                ) cm_max ON tr.req_id = cm_max.req_id
                WHERE tr.staff_id = ?
                ORDER BY cm_max.last_message_id DESC
            ", [$staff_id]));

            $this->countUnread = collect(DB::select("
                select cm.req_id, count(cm.req_id) as unread_count
                from conversation_message cm
                join technical_request tr on cm.req_id = tr.req_id
                join staff s on tr.staff_id = s.staff_id
                where cm.msg_seen_at is null
                and cm.sender_type = 'super'
                and s.staff_id = ?
                group by cm.req_id
            ", [$staff_id]))->pluck('unread_count', 'req_id');
        }
    }

    public function show($req_id = null) {
        $this->recentreq = $req_id 
            ? $this->requests->where('req_id', $req_id)->first()
            : $this->requests->first();

        if ($this->recentreq) {
            $this->refreshConvo($req_id);
        }

        DB::update("
            update conversation_message
            set msg_seen_at = ?
            where msg_seen_at is null
            and req_id = ?
        ", [NOW(), $req_id]);
    }

    public function refreshConvo($req_id = null) {
        if (!$req_id) return;

        $this->recentreq = $this->requests->where('req_id', $req_id)->first();

        if (!$this->recentreq) return;

        if (Auth::guard('owner')->check()) {
            $owner_id = Auth::guard('owner')->user()->owner_id;
            $this->convos = collect(DB::select("
                SELECT cm.*, tr.req_title, tr.req_status, tr.req_id
                FROM technical_request tr
                LEFT JOIN conversation_message cm ON tr.req_id = cm.req_id
                WHERE tr.owner_id = ?
                AND tr.req_id = ?
                ORDER BY cm.msg_date DESC
            ", [$owner_id, $req_id]));
        }

        if (Auth::guard('staff')->check()) {
            $staff_id = Auth::guard('staff')->user()->staff_id;
            $this->convos = collect(DB::select("
                SELECT cm.*, tr.req_title, tr.req_status
                FROM technical_request tr
                LEFT JOIN conversation_message cm ON tr.req_id = cm.req_id
                WHERE tr.staff_id = ?
                AND tr.req_id = ?
                ORDER BY cm.msg_date DESC
            ", [$staff_id, $req_id]));
        }
        
    }


    public function sendMessage($req_id)
    {
        
        if (Auth::guard('owner')->check()) {
            $owner = Auth::guard('owner')->user();
            $owner_id = $owner->owner_id;

            if (trim($this->newMessage) === '') {
                return;
            }

            DB::insert("
            INSERT INTO conversation_message (sender_type, sender_id, message, msg_date, req_id)
            VALUES ('owner', ?, ?, NOW(), ?)", [ $owner_id, $this->newMessage, $req_id ]);

            $this->refreshConvo($req_id);
            $this->reset('newMessage');
            $this->newMessage = '';
        }

        if (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();
            $staff_id = $staff->staff_id;

            if (trim($this->newMessage) === '') {
                return;
            }

            DB::insert("
            INSERT INTO conversation_message (sender_type, sender_id, message, msg_date, req_id)
            VALUES ('staff', ?, ?, NOW(), ?)", [ $staff_id, $this->newMessage, $req_id ]);

            $this->refreshConvo($req_id);
            $this->reset('newMessage');
            $this->newMessage = '';
        }

        
    }





    public function addModal() {
        $this->addModalOpen = true;
    }

    public function closeModal() {
        $this->addModalOpen = false;
    }


    public function addRequest() {

        if (Auth::guard('owner')->check()) {
            $owner = Auth::guard('owner')->user();
            $owner_id = $owner->owner_id;

            if (trim($this->addRequestTitle) === '' || trim($this->addRequestMessage) === '') {
                return;
            }

            $req_ticket = $this->generateTicket();

            DB::insert("
                INSERT INTO technical_request (req_title, req_date, req_status, req_ticket, owner_id)
                VALUES (?, CURDATE(), 'Pending', ?, ?)
            ", [$this->addRequestTitle, $req_ticket, $owner_id]);

            $req_id = DB::getPdo()->lastInsertId();

            
            DB::insert("
                INSERT INTO conversation_message (req_id, sender_type, sender_id, message, msg_date)
                VALUES (?, 'owner', ?, ?, NOW())
            ", [$req_id, $owner_id, $this->addRequestMessage]);

        }

        
        if (Auth::guard('staff')->check()) {
            $staff = Auth::guard('staff')->user();
            $staff_id = $staff->staff_id;

            if (trim($this->addRequestTitle) === '' || trim($this->addRequestMessage) === '') {
                return;
            }

            $req_ticket = $this->generateTicket();

            DB::insert("
                INSERT INTO technical_request (req_title, req_date, req_status, req_ticket, staff_id)
                VALUES (?, CURDATE(), 'Pending', ?, ?)
            ", [$this->addRequestTitle, $req_ticket, $staff_id]);

            $req_id = DB::getPdo()->lastInsertId();

            
            DB::insert("
                INSERT INTO conversation_message (req_id, sender_type, sender_id, message, msg_date)
                VALUES (?, 'staff', ?, ?, NOW())
            ", [$req_id, $staff_id, $this->addRequestMessage]);
            
        }

        $this->loadRequests();
        $this->addModalOpen = false;

    }

    public function generateTicket() {
        do {
            $ticket = strtolower(
                Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)
            );

            $result = DB::select("
                SELECT COUNT(*) as count 
                FROM technical_request 
                WHERE req_ticket = ?
            ", [$ticket]);

        } while ($result[0]->count > 0);

        return $ticket;
    }

    public function render()
    {
        $this->loadRequests(); 
        return view('livewire.technical-request');
    }
}
