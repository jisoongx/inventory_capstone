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
        'discount_value',
        'discount_amount' // ✅ Added - stores calculated discount amount
    ];
    
    protected $casts = [
        'receipt_date' => 'datetime',
        'amount_paid' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2' // ✅ Added
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
     * Now uses the stored discount_amount instead of recalculating
     */
    public function getTotalAmountAttribute()
    {
        // Calculate subtotal (sum of all items after item-level discounts)
        $subtotal = $this->receiptItems()->with('product')->get()->sum(function ($item) {
            $lineTotal = $item->item_quantity * $item->product->selling_price;
            
            // ✅ Use stored item_discount_amount if available, otherwise calculate
            $itemDiscount = $item->item_discount_amount ??  0;
            if ($itemDiscount == 0 && $item->item_discount_value > 0) {
                // Fallback calculation if discount_amount not stored
                if ($item->item_discount_type == 'percent') {
                    $itemDiscount = $lineTotal * ($item->item_discount_value / 100);
                } else {
                    $itemDiscount = $item->item_discount_value;
                }
            }
            
            return $lineTotal - $itemDiscount;
        });

        // ✅ Use stored discount_amount instead of recalculating
        $receiptDiscount = $this->discount_amount ?? 0;

        return $subtotal - $receiptDiscount;
    }

    /**
     * Get subtotal before receipt-level discount
     */
    public function getSubtotalAttribute()
    {
        return $this->receiptItems()->with('product')->get()->sum(function ($item) {
            $lineTotal = $item->item_quantity * $item->product->selling_price;
            
            // ✅ Use stored item_discount_amount if available
            $itemDiscount = $item->item_discount_amount ?? 0;
            if ($itemDiscount == 0 && $item->item_discount_value > 0) {
                if ($item->item_discount_type == 'percent') {
                    $itemDiscount = $lineTotal * ($item->item_discount_value / 100);
                } else {
                    $itemDiscount = $item->item_discount_value;
                }
            }
            
            return $lineTotal - $itemDiscount;
        });
    }

    /**
     * Get receipt-level discount amount
     * ✅ Now returns the stored calculated amount
     */
    public function getReceiptDiscountAmountAttribute()
    {
        // Return stored amount if available
        if (isset($this->attributes['discount_amount'])) {
            return $this->attributes['discount_amount'];
        }
        
        // Fallback calculation for old records
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

    /**
     * ✅ Get total item discounts (sum of all item-level discounts)
     */
    public function getTotalItemDiscountsAttribute()
    {
        return $this->receiptItems()->get()->sum(function ($item) {
            return $item->item_discount_amount ?? 0;
        });
    }

    /**
     * ✅ Check if receipt has item-level discounts
     */
    public function hasItemDiscounts()
    {
        return $this->total_item_discounts > 0;
    }

    /**
     * ✅ Check if receipt has receipt-level discount
     */
    public function hasReceiptDiscount()
    {
        return ($this->discount_amount ??  0) > 0;
    }
}