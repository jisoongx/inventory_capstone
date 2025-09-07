<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $table = 'receipt';
    protected $primaryKey = 'receipt_id';
    public $timestamps = false;

    protected $fillable = [
        'receipt_date',
        'owner_id',
        'staff_id'
    ];

    protected $casts = [
        'receipt_date' => 'datetime'
    ];

    // Relationships
    public function receiptItems()
    {
        return $this->hasMany(ReceiptItem::class, 'receipt_id', 'receipt_id');
    }

    public function getTotalAmountAttribute()
    {
        return $this->receiptItems()->with('product')->get()->sum(function ($item) {
            return $item->item_quantity * $item->product->selling_price;
        });
    }

    public function getTotalQuantityAttribute()
    {
        return $this->receiptItems()->sum('item_quantity');
    }
}