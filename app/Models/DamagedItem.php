<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamagedItem extends Model
{
    protected $table = 'damaged_items';
    protected $primaryKey = 'damaged_id';
    public $timestamps = false;
    
    protected $fillable = [
        'prod_code',
        'damaged_quantity',
        'damaged_date',
        'damaged_reason',
        'return_id',
        'owner_id',
        'staff_id'
    ];

    protected $casts = [
        'damaged_date' => 'datetime',
        'damaged_quantity' => 'integer'
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class, 'prod_code', 'prod_code');
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}