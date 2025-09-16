<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TechnicalResolve extends Component
{
    public $statusFilter = null;
    public $request = [];
    public $countUnread = null;

    public $searchWord = null;

    public $convos = [];

    public $currentConvoId = null;

    public $showModal = false;
    public $newMessage = '';
    public $ticket = ''; 
    public $showStatus = '';

    public $showOption = false;

    public $showSuccess = false;
    

    public function mount()
    {
        if (!Auth::guard('super_admin')->check()) {
            abort(403, 'Access not available');
        } 

        $this->loadRequest();
    }

    public function updatedSearchWord() {
        $like = "%{$this->searchWord}%";

        $this->request = collect(DB::select("
            SELECT tr.req_ticket, tr.req_date, req_status, tr.req_title, cm.message, o.firstname, o.lastname, tr.req_id
            FROM conversation_message cm
            JOIN technical_request tr ON cm.req_id = tr.req_id
            JOIN owners o ON o.owner_id = tr.owner_id
            WHERE o.firstname LIKE ?
            OR o.lastname LIKE ?
            OR cm.message LIKE ?
            OR tr.req_title LIKE ?
            ORDER BY tr.req_id DESC
        ", [$like, $like, $like, $like]));
    }

    public function filterByStatus($status) {
        $this->statusFilter = $status;

        $this->loadRequest(); 
    }


    public function loadRequest()
    {

        if ($this->statusFilter) {
            $req= "SELECT tr.*, cm_max.last_message_id, cm_max.last_message_date
                FROM technical_request tr
                JOIN (
                    SELECT req_id, MAX(msg_id) AS last_message_id, MAX(msg_date) as last_message_date
                    FROM conversation_message
                    GROUP BY req_id
                ) cm_max ON tr.req_id = cm_max.req_id
                WHERE req_status = ?
                ORDER BY cm_max.last_message_id DESC";
            $this->request = collect(DB::select($req, [$this->statusFilter]));

        } else {
            $req = "SELECT tr.*, cm_max.last_message_id, cm_max.last_message_date
                FROM technical_request tr
                JOIN (
                    SELECT req_id, MAX(msg_id) AS last_message_id, MAX(msg_date) as last_message_date
                    FROM conversation_message
                    GROUP BY req_id
                ) cm_max ON tr.req_id = cm_max.req_id
                ORDER BY cm_max.last_message_id DESC";
            $this->request = collect(DB::select($req));
        }

        $this->countUnread = collect(DB::select("
            select cm.req_id, count(cm.req_id) as unread_count
            from conversation_message cm
            join technical_request tr on cm.req_id = tr.req_id
            where cm.msg_seen_at is null
            and cm.sender_type in ('owner', 'staff')
            group by cm.req_id
        "))->pluck('unread_count', 'req_id');

    }



    
    public function closeOption() {
        $this->showOption = false;
    }

    public function openOption() {
        $this->showOption = true;
    }

    public function markAsResolved($req_id) {

        if (!Auth::guard('super_admin')->check()) {
            abort(403, 'Access not available');
        } 
        
        DB::update("
            update technical_request
            set req_status = 'Resolved'
            where req_id = ? ", [$req_id]);

        $this->loadConversation($req_id);
        $this->showSuccess = true;

    }





    public function sendMessage($req_id) {

        if (!Auth::guard('super_admin')->check()) {
            abort(403, 'Access not available');
        }

        if (trim($this->newMessage) === '') {
            return;
        }

        $super = Auth::guard('super_admin')->user();
        $super_id = $super->super_id;

        DB::insert("
            INSERT INTO conversation_message (sender_type, sender_id, message, msg_date, req_id)
            VALUES ('super', ?, ?, NOW(), ?)", [ $super_id, $this->newMessage, $req_id ]);

        DB::update("
            update technical_request
            set req_status = 'In Progress'
            where req_id = ?", [$req_id]);

        
        $this->reset('newMessage');

        $this->refreshConversation($req_id);
    }




    

    public function closeModal() {
        $this->showModal = false;
    }


    public function openModal($req_id) {

        if (!Auth::guard('super_admin')->check()) {
            abort(403, 'Access not available');
        } 

        $this->loadConversation($req_id);
        $this->showModal = true;
    }


    public function loadConversation($req_id) {

        $this->currentConvoId = $req_id;

        $role = DB::selectOne("
            SELECT 
                CASE 
                    WHEN owner_id IS NOT NULL THEN 'owner'
                    WHEN staff_id IS NOT NULL THEN 'staff'
                    ELSE 'unknown'
                END AS role
            FROM technical_request
            WHERE req_id = ?
        ", [$req_id]);

        if ($role->role === "owner") {
            $ticket = DB::selectOne("
                SELECT tr.req_id, tr.req_ticket, tr.req_title, tr.req_status,
                    o.firstname, o.lastname, o.store_name
                FROM technical_request tr
                JOIN owners o ON o.owner_id = tr.owner_id
                WHERE tr.req_id = ?
            ", [$req_id]);

            $this->convos = collect(DB::select("
                SELECT cm.*, tr.req_id, tr.req_title, tr.req_status, o.firstname, o.lastname, o.store_name, tr.req_ticket
                FROM technical_request tr
                LEFT JOIN conversation_message cm ON tr.req_id = cm.req_id
                JOIN owners o ON o.owner_id = tr.owner_id
                WHERE tr.req_id = ?
                ORDER BY cm.msg_date DESC
            ", [$req_id]));

            $this->ticket = $ticket;

        } elseif ($role->role === "staff") {
            $ticket = DB::selectOne("
                SELECT tr.req_id, tr.req_ticket, tr.req_title, tr.req_status,
                    s.firstname, s.lastname, o.store_name
                FROM technical_request tr
                JOIN staff s ON s.staff_id = tr.staff_id
                JOIN owners o ON s.owner_id = o.owner_id
                WHERE tr.req_id = ?
            ", [$req_id]);

            $this->convos = collect(DB::select("
                SELECT cm.*, tr.req_id, tr.req_title, tr.req_status, s.firstname, s.lastname, o.store_name, tr.req_ticket
                FROM technical_request tr
                LEFT JOIN conversation_message cm ON tr.req_id = cm.req_id
                JOIN staff s ON s.staff_id = tr.staff_id
                JOIN owners o ON s.owner_id = o.owner_id
                WHERE tr.req_id = ?
                ORDER BY cm.msg_date DESC
            ", [$req_id]));

            $this->ticket = $ticket;
        }
    }



    public function refreshConversation() {

        if (!$this->currentConvoId) {
            return;
        }

         $this->convos = collect(DB::select("
            SELECT cm.*, tr.req_title, tr.req_status
            FROM technical_request tr
            LEFT JOIN conversation_message cm ON tr.req_id = cm.req_id
            WHERE tr.req_id = ?
            ORDER BY cm.msg_date DESC
        ", [$this->currentConvoId]));
    }



    public function refreshRequests() {
        $this->loadRequest();
    }


    public function render() {
        $this->loadRequest();
        return view('livewire.technical-resolve');
    }
}
