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
        $owner_id = session('owner_id');
    
        if (!$owner_id) {
            return $this->isExpired = false;
        }

        $owner = collect(DB::select("
            select o.owner_id, s.subscription_end 
            from owners o
            join subscriptions s on o.owner_id = s.owner_id
            where o.owner_id = ?
            order by subscription_id desc
            limit 1", [$owner_id]))->first();

        if ($owner && $owner->subscription_end !== null && $owner->subscription_end <= date('Y-m-d')) {
            return $this->isExpired = true;
        }
    }

    public function render()
    {
        $this->expirationTracker();
        return view('livewire.expiration-container');
    }
}
