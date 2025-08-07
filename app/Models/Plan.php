<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'plans';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'plan_id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true; // Assuming plan_id is auto-incrementing

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plan_title',
        'plan_price',
        'plan_includes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'plan_price' => 'decimal:2',
    ];

    // --- Relationships ---

    /**
     * Get the subscriptions for the plan.
     */
    public function subscriptions()
    {
        // 'plan_id' is the foreign key on the subscriptions table
        // 'plan_id' is the local primary key on this (plans) table
        return $this->hasMany(Subscription::class, 'plan_id', 'plan_id');
    }
}