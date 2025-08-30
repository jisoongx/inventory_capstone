<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class SuperAdmin extends Authenticatable
{
    use Notifiable;
    protected $table = 'super_admin';
    protected $primaryKey = 'super_id';
    public $timestamps = false;

    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'email',
        'contact',
        'super_pass',
    ];


    protected $hidden = [
        'super_pass',
    ];

    public function getAuthPassword()
    {
        return $this->super_pass;
    }

    public function getAuthPasswordName()
    {
        return 'super_pass';
    }
}
