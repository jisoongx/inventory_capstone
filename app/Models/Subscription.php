<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Subscription extends Model
{

    protected $table = 'subscriptions';
    protected $primaryKey = 'subscription_id';
    public $timestamps = false;

    protected $fillable = [
        'subscription_start',
        'subscription_end',
        'plan_id',
        'owner_id',
        'status',
        'progress_view'
    ];

    protected $casts = [
        'progress_view' => 'boolean',
        'subscription_start' => 'datetime',
        'subscription_end'   => 'datetime'
    ];

    
    
    public function isActive()
    {
        return $this->status === 'active'
            && Carbon::now()->between(
                Carbon::parse($this->subscription_start),
                Carbon::parse($this->subscription_end)
            );
    }



    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function planDetails()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'plan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'subscription_id', 'subscription_id');
        // if your PK in subscriptions is subscription_id, use that instead of 'id'
    }
}
