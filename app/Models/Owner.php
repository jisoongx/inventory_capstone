<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class Owner extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'owners'; // Assuming your owner table is named 'owners'
    protected $primaryKey = 'owner_id'; // Assuming owner_id is your primary key
    public $incrementing = true;
    public $timestamps = false; // If you don't use Laravel's default timestamps

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'email',
        'contact',
        'store_name',
        'store_address',
        'owner_pass', // Assuming 'owner_pass' is the column name for owner password
        'status',    // Make sure 'status' is fillable if you're using it
        'email_verified_at',
        // Add any other fillable fields here
    ];

    protected $hidden = [
        'owner_pass',
        'remember_token', // If you're using remember me functionality
    ];

    /**
     * Get the password for the owner.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->owner_pass; // Return the value of your 'owner_pass' column
    }

    /**
     * Get the subscriptions for the owner.
     */
    public function subscriptions()
    {
        // An Owner has many Subscriptions.
        // 'owner_id' is the foreign key on the 'subscription' table.
        // 'owner_id' is the local key on the 'owners' table (assuming it's your primary key).
        return $this->hasMany(Subscription::class, 'owner_id', 'owner_id');
    }

    /**
     * Get the currently active subscription for the owner.
     */
    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class, 'owner_id') // ← Explicitly set the foreign key
            ->where('status', 'paid')
            ->where('subscription_end', '>=', now());
    }


    /**
     * Get the staff members for the owner.
     */
    public function staff()
    {
        // An Owner has many Staff members.
        // 'owner_id' is the foreign key on the 'staff' table.
        // 'owner_id' is the local key on the 'owners' table.
        return $this->hasMany(Staff::class, 'owner_id', 'owner_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'owner_id', 'owner_id');
    }
}