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
        'staff_id',
        'amount_paid',
        'discount_type',
        'discount_value'
    ];
    
    protected $casts = [
        'receipt_date' => 'datetime',
        'amount_paid' => 'decimal:2',
        'discount_value' => 'decimal:2'
    ];

    // Relationships
    public function receiptItems()
    {
        return $this->hasMany(ReceiptItem::class, 'receipt_id', 'receipt_id');
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class, 'owner_id', 'owner_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    /**
     * Calculate total amount after all discounts
     * This matches the calculation logic in your views
     */
    public function getTotalAmountAttribute()
    {
        // Calculate subtotal after item-level discounts
        $subtotal = $this->receiptItems()->with('product')->get()->sum(function ($item) {
            $lineTotal = $item->item_quantity * $item->product->selling_price;
            
            // Apply item-level discount
            $itemDiscount = 0;
            if ($item->item_discount_type == 'percent') {
                $itemDiscount = $lineTotal * ($item->item_discount_value / 100);
            } else {
                $itemDiscount = $item->item_discount_value;
            }
            
            return $lineTotal - $itemDiscount;
        });

        // Apply receipt-level discount
        $receiptDiscount = 0;
        if ($this->discount_type == 'percent') {
            $receiptDiscount = $subtotal * ($this->discount_value / 100);
        } else {
            $receiptDiscount = $this->discount_value;
        }

        return $subtotal - $receiptDiscount;
    }

    /**
     * Get subtotal before receipt-level discount
     */
    public function getSubtotalAttribute()
    {
        return $this->receiptItems()->with('product')->get()->sum(function ($item) {
            $lineTotal = $item->item_quantity * $item->product->selling_price;
            
            // Apply item-level discount
            $itemDiscount = 0;
            if ($item->item_discount_type == 'percent') {
                $itemDiscount = $lineTotal * ($item->item_discount_value / 100);
            } else {
                $itemDiscount = $item->item_discount_value;
            }
            
            return $lineTotal - $itemDiscount;
        });
    }

    /**
     * Get receipt-level discount amount
     */
    public function getReceiptDiscountAmountAttribute()
    {
        $subtotal = $this->subtotal;
        
        if ($this->discount_type == 'percent') {
            return $subtotal * ($this->discount_value / 100);
        }
        
        return $this->discount_value;
    }

    /**
     * Get change amount
     */
    public function getChangeAttribute()
    {
        return $this->amount_paid - $this->total_amount;
    }

    /**
     * Get total quantity of items
     */
    public function getTotalQuantityAttribute()
    {
        return $this->receiptItems()->sum('item_quantity');
    }

    /**
     * Get total number of unique items
     */
    public function getTotalItemsAttribute()
    {
        return $this->receiptItems()->count();
    }
}