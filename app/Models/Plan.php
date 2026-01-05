<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'plans';
    protected $primaryKey = 'plan_id';

    protected $fillable = [
        'plan_title',
        'plan_price',
        'plan_includes',
        'plan_duration_months',
        'is_active',
        'paypal_plan_id',
        'paypal_product_id'
    ];

    protected $casts = [
        'plan_price' => 'decimal:2',
        'plan_duration_months' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id', 'plan_id');
    }
}
