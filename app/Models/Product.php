<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'prod_code';
    public $timestamps = false;

    protected $fillable = [
        'barcode',
        'name',
        'cost_price',
        'selling_price',
        'description',
        'owner_id',
        'staff_id',
        'category_id',
        'unit_id',
        'quantity',
        'prod_image'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'integer'
    ];

    // Relationships
    public function receiptItems()
    {
        return $this->hasMany(ReceiptItem::class, 'prod_code', 'prod_code');
    }
}