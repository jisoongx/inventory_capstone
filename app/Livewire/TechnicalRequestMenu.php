<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class TechnicalRequestMenu extends Component
{
    public $newMessage = ''; 
    public $countUnread = null;

    public function mount() {
        $this->getNew();
    }

    public function getNew() {
        if (Auth::guard('owner')->check()) {
            $owner_id = Auth::guard('owner')->user()->owner_id;

            $this->countUnread = DB::selectOne("
                select count(*) as unread_count
                from conversation_message cm
                join technical_request tr on cm.req_id = tr.req_id
                join owners o on tr.owner_id = o.owner_id
                where cm.msg_seen_at is null
                and cm.sender_type = 'super'
                and o.owner_id = ?
            ", [$owner_id])->unread_count;

        }

        if (Auth::guard('staff')->check()) {
            $staff_id = Auth::guard('staff')->user()->staff_id;

            $this->countUnread = DB::selectOne("
                select count(*) as unread_count
                from conversation_message cm
                join technical_request tr on cm.req_id = tr.req_id
                join staff s on tr.staff_id = s.staff_id
                where cm.msg_seen_at is null
                and cm.sender_type = 'super'
                and s.staff_id = ?
            ", [$staff_id])->unread_count;

        }
    }

    public function render() {
        $this->getNew();
        return view('livewire.technical-request-menu');
    }
}
