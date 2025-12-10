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
        // Determine owner_id based on login type
        if (Auth::guard('owner')->check()) {
            $owner_id = Auth::guard('owner')->id();
        } elseif (Auth::guard('staff')->check()) {
            // Get the owner_id associated with the staff
            $owner_id = Auth::guard('staff')->user()->owner_id;
        } else {
            $this->isExpired = false;
            return;
        }

        // Fetch the latest subscription for this owner
        $owner = collect(DB::select("
            SELECT o.owner_id, s.subscription_end
            FROM owners o
            JOIN subscriptions s ON o.owner_id = s.owner_id
            WHERE o.owner_id = ?
            ORDER BY s.subscription_id DESC
            LIMIT 1
        ", [$owner_id]))->first();

        // Check if subscription is expired
        if ($owner && $owner->subscription_end !== null && $owner->subscription_end <= date('Y-m-d')) {
            $this->isExpired = true;
        } else {
            $this->isExpired = false;
        }
    }

    public function render()
    {
        $this->expirationTracker();
        return view('livewire.expiration-container');
    }
}
