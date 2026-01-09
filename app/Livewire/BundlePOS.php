<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class BundlePOS extends Component
{
    public $bundles = [];
    public $showBundles = false; 
    public $selectedBundle = null;

    // fetch bundles only when button clicked
    public function loadBundles()
    {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        if (!Auth::guard('owner')->check()) {
            abort(403, 'Unauthorized access.');
        }

        $this->bundles = DB::select("
            SELECT b.bundle_id,
                b.bundle_code,
                b.bundle_name,
                b.bundle_category,
                b.bundle_type,
                b.status,
                b.owner_id,
                s.discount_percent,
                s.min_margin,
                s.start_date,
                s.end_date
            FROM bundles b
            LEFT JOIN bundles_setting s
                ON b.bundle_id = s.bundle_id
            where owner_id = ?
                and status = 'active'
        ", [$owner_id]);
        $this->showBundles = true;
    }

    public function selectBundle($bundleId)
    {
        $this->selectedBundle = DB::select("
            SELECT b.bundle_id,
                b.bundle_code,
                b.bundle_name,
                b.bundle_category,
                b.bundle_type,
                b.status,
                b.owner_id,
                s.discount_percent,
                s.min_margin,
                s.start_date,
                s.end_date,
                p.name, 
                bi.quantity
            FROM bundles b
            JOIN bundles_setting s
                ON b.bundle_id = s.bundle_id
            JOIN bundle_items bi 
                ON b.bundle_id = bi.bundle_id
            JOIN products p 
                ON p.prod_code = bi.prod_code
            where b.bundle_id = ?
        ", [$bundleId]);
        
        $this->showBundles = false;
    }

    public function confirmBundle()
    {
        $this->dispatchBrowserEvent('bundle-selected', [
            'bundle' => $this->selectedBundle
        ]);

        $this->selectedBundle = null;
    }

    public function render()
    {
        return view('livewire.bundle-POS');
    }
}
