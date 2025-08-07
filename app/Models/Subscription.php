<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Subscription extends Model
{
    // REMOVE THIS LINE: protected $table = 'subscription';
    // Laravel will now correctly infer 'subscriptions' from the model name.

    protected $primaryKey = 'subscription_id';

    public $timestamps = false;

    protected $fillable = [
        'subscription_start',
        'subscription_end',
        'plan_id',
        'owner_id',
        'status',
    ];

    public function isActive()
    {
        return Carbon::now()->lt(Carbon::parse($this->subscription_end));
    }


    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }
    public function planDetails()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'plan_id');
    }
    public function handle()
    {
        $now = Carbon::now();

        $expired = Subscription::where('status', 'paid') // lowercase 'paid'
            ->whereDate('subscription_end', '<', $now)
            ->update(['status' => 'expired']); // lowercase 'expired'

        $this->info("Marked {$expired} subscriptions as expired.");
    }
}