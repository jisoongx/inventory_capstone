<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpirationContainer extends Component
{   
    public $isExpired = false;

    public function mount() {        
        $this->expirationTracker();
    }

    private function expirationTracker() {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        $owner = collect(DB::select("
            select o.owner_id, s.subscription_end 
            from owners o
            join subscriptions s on o.owner_id = s.owner_id
            where o.owner_id = ?
            order by subscription_id desc
            limit 1", [$owner_id]))->first();

        if ($owner->subscription_end <= date('Y-m-d')) {
            return $this->isExpired = true;
        }

    }

    public function render()
    {
        $this->expirationTracker();
        return view('livewire.expiration-container');
    }
}
