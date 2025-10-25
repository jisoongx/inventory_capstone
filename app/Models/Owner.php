<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str; // ✅ for random token generation
use App\Models\Subscription;

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
        'verification_token', // ✅ make sure this is fillable
    ];

    protected $hidden = [
        'owner_pass',
        'verification_token', // keep it hidden from accidental exposure
    ];

    // ✅ Authentication password field (custom column)
    public function getAuthPassword()
    {
        return $this->owner_pass;
    }

    // ✅ Email verification helpers
    public function generateVerificationToken()
    {
        $this->verification_token = Str::random(64);
        $this->save();
    }

    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    public function markEmailAsVerified()
    {
        $this->email_verified_at = now();
        $this->verification_token = null;
        $this->save();
    }

    // ✅ Relationships
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'owner_id', 'owner_id');
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class, 'owner_id')
            ->where('status', 'active')
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


    public function generatePasswordResetToken()
    {
        $this->reset_token = Str::random(64);
        $this->reset_token_expires_at = now()->addMinutes(60);
        $this->save();

        return $this->reset_token;
    }

    public function clearPasswordResetToken()
    {
        $this->reset_token = null;
        $this->reset_token_expires_at = null;
        $this->save();
    }
}

