<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $table = 'plans';
    protected $primaryKey = 'plan_id';
    protected $fillable = [
        'plan_title',
        'plan_price',
        'plan_includes',
    ];

    protected $casts = [
        'plan_price' => 'decimal:2',
    ];

    
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id', 'plan_id');
    }
}