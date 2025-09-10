<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TechnicalResolve extends Component
{
    public $statusFilter = null;
    public $request = [];
    public $searchWord = null;

    public $convos = [];

    public $showModal = false;
    public $newMessage = '';
    public $ticket = ''; 
    public $showStatus = '';

    public $showOption = false;
    

    public function mount()
    {
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
            $req= "SELECT req_ticket, req_title, req_date, req_status, req_id
                  FROM technical_request WHERE req_status = ? ORDER BY req_id DESC";
            $this->request = collect(DB::select($req, [$this->statusFilter]));
        } else {
            $req = "SELECT req_ticket, req_title, req_date, req_status, req_id
                  FROM technical_request ORDER BY req_id DESC";
            $this->request = collect(DB::select($req));
        }

    }

    
    public function closeModal() {
        $this->showModal = false;
    }


    public function openModal($req_id) {

        if (!Auth::guard('super_admin')->check()) {
            abort(403, 'Access not available');
        } 

        $ticket = DB::selectOne("
            SELECT tr.req_id, tr.req_ticket, tr.req_title, tr.req_status,
                o.firstname, o.lastname, o.store_name
            FROM technical_request tr
            JOIN owners o ON o.owner_id = tr.owner_id
            WHERE tr.req_id = ?
        ", [$req_id]);


        $this->convos = collect(DB::select("
            SELECT cm.*, tr.req_title, tr.req_status, o.firstname, o.lastname, o.store_name, tr.req_ticket, o.store_name
            FROM technical_request tr
            LEFT JOIN conversation_message cm
                ON tr.req_id = cm.req_id
            JOIN owners o on o.owner_id = tr.owner_id
            WHERE tr.req_id = ?
            ORDER BY cm.msg_date ASC
        ", [$req_id]));

        $this->ticket = $ticket;
        $this->showModal = true;
        
    }


    
    public function closeOption() {
        $this->showOption = false;
    }

    public function openOption() {
        if (!Auth::guard('super_admin')->check()) {
            abort(403, 'Access not available');
        } 

        $this->showOption = true;
    }

    public function markAsResolved($req_id) {

        if (!Auth::guard('super_admin')->check()) {
            abort(403, 'Access not available');
        } 
        
        DB::update("
            update technical_request
            set req_status = 'In Progress'
            where req_id = ? ", [$req_id]);

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


    public function loadConversation($req_id) {

        $ticket = DB::select("
            SELECT tr.req_id, tr.req_ticket, tr.req_title, tr.req_status,
                o.firstname, o.lastname, o.store_name
            FROM technical_request tr
            JOIN owners o ON o.owner_id = tr.owner_id
            WHERE tr.req_id = ?
        ", [$req_id]);
        
        $this->convos = collect(DB::select("
            SELECT cm.*, tr.req_title, tr.req_status, o.firstname, o.lastname, o.store_name, tr.req_ticket, o.store_name, tr.req_id
            FROM technical_request tr
            LEFT JOIN conversation_message cm
                ON tr.req_id = cm.req_id
            JOIN owners o on o.owner_id = tr.owner_id
            WHERE tr.req_id = ?
            ORDER BY cm.msg_date ASC
        ", [$req_id]));

        $this->ticket = $ticket;

    }


    public function refreshConversation($req_id) {
        $messages = collect(DB::select("
            SELECT cm.*
            FROM conversation_message cm
            WHERE cm.req_id = ?
            ORDER BY cm.msg_date ASC
        ", [$req_id]));

        $this->convos = $messages;
    }



    public function refreshRequests() {
        $this->loadRequest();
    }


    public function render() {
        return view('livewire.technical-resolve');
    }
}
