<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'inven_code';
    
    // Add these timestamp configurations
    const CREATED_AT = null; // No created_at column in your table
    const UPDATED_AT = 'last_updated'; // Use last_updated instead of updated_at
    
    protected $fillable = [
        'stock',
        'date_added',
        'expiration_date',
        'batch_number',
        'owner_id',
        'prod_code',
        'category_id'
    ];

    protected $casts = [
        'date_added' => 'date',
        'expiration_date' => 'date',
        'last_updated' => 'datetime'
    ];

    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class, 'prod_code', 'prod_code');
    }
}