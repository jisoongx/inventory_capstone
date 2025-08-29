<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActLog extends Model
{
    protected $table = 'act_logs';
    public $timestamps = false;

    protected $primaryKey = 'log_id';

    protected $fillable = [
        'log_timestamp', 
        'log_type',
        'staff_id',
        'owner_id',
        'super_id',
        'log_location'
    ];

    protected $casts = [
        'log_timestamp' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function superAdmin()
    {
        return $this->belongsTo(SuperAdmin::class, 'super_id');
    }
}