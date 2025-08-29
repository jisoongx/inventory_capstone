<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

use App\Models\Owner; 

class Staff extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'staff';
    protected $primaryKey = 'staff_id'; 
    public $timestamps = false; 

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'email',
        'contact',
        'staff_pass',
        'owner_id',
    ];

    protected $hidden = [
        'staff_pass',
        'verification_token',
    ];

    public function getAuthPassword()
    {
        return $this->staff_pass; 
    }

    public function getAuthPasswordName()
    {
        return 'staff_pass'; 
    }
    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id', 'owner_id');
    }
}