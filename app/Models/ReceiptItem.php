<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceiptItem extends Model
{
    protected $table = 'receipt_item';
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'item_quantity',
        'prod_code',
        'receipt_id'
    ];

    protected $casts = [
        'item_quantity' => 'integer'
    ];

    // Relationships
    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id', 'receipt_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'prod_code', 'prod_code');
    }

    public function getTotalAmountAttribute()
    {
        return $this->item_quantity * $this->product->selling_price;
    }
}