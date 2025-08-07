<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SuperAdmin extends Authenticatable
{
    use Notifiable;

    // ✅ Specify custom table name
    protected $table = 'super_admin';

    // ✅ Use non-default primary key
    protected $primaryKey = 'super_id';

    // ✅ Disable incrementing if not auto-increment (optional)
    public $incrementing = true;

    // ✅ If your primary key is not an integer, set this to false
    protected $keyType = 'int';

    // ✅ Automatically manage timestamps?
    public $timestamps = false;

    // ✅ Mass assignable fields
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'email',
        'contact',
        'super_pass', // This is your password column
    ];

    // ✅ Hide sensitive fields when returning JSON
    protected $hidden = [
        'super_pass',
    ];

    // ✅ Tell Laravel what the password field is for Auth
    public function getAuthPassword()
    {
        return $this->super_pass; // Return the value of your 'staff_pass' column
    }

    public function getAuthPasswordName()
    {
        return 'super_pass'; // Explicitly return your password column name
    }
}