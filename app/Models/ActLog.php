<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActLog extends Model
{
    protected $table = 'act_logs';
    public $timestamps = false;

    protected $primaryKey = 'log_id';

    protected $fillable = [
        'log-timestamp', // Keep as-is if column name really uses hyphen
        'log_type',
        'staff_id',
        'owner_id',
        'super_id',
    ];

    // Optionally: use attribute casting if needed
    protected $casts = [
        'log-timestamp' => 'datetime', // Laravel will still accept it, but use quotes
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