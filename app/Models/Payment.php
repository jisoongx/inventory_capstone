<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payment';
    protected $primaryKey = 'payment_id';
    public $timestamps = false;

    protected $fillable = [
        'owner_id',
        'subscription_id',   // 👈 add this
        'payment_mode',
        'payment_acc_number',
        'payment_amount',
        'payment_date',
    ];

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id', 'owner_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class, 'subscription_id', 'subscription_id');
        // 👆 adjust 'id' if your subscriptions table uses 'subscription_id' instead of the default 'id'
    }

    protected $casts = [
        'payment_date' => 'datetime',
    ];
}
