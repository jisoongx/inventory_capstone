<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Owner extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'owners';
    protected $primaryKey = 'owner_id';
    public $timestamps = false;
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'email',
        'contact',
        'store_name',
        'store_address',
        'owner_pass',
        'status',
        'email_verified_at',

    ];

    protected $hidden = [
        'owner_pass'
    ];


    public function getAuthPassword()
    {
        return $this->owner_pass;
    }


    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'owner_id', 'owner_id');
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class, 'owner_id')
            ->where('status', 'paid')
            ->where('subscription_end', '>=', now());
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'owner_id', 'owner_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'owner_id', 'owner_id');
    }


    public function latestSubscription()
    {
        return $this->hasOne(Subscription::class, 'owner_id', 'owner_id')
            ->latest('subscription_start');
    }
    public function payments()
    {
        return $this->hasMany(Payment::class, 'owner_id');
    }

    public function getEmailForPasswordReset()
    {
        return $this->email; // your email column
    }
}
