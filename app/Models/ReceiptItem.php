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
        'receipt_id',
        'item_discount_type',
        'item_discount_value',
        'item_discount_amount', // ✅ Added - stores calculated item discount amount
        'vat_amount',
        'inven_code',
    ];
    
    protected $casts = [
        'item_quantity' => 'integer',
        'item_discount_value' => 'decimal:2',
        'item_discount_amount' => 'decimal:2', // ✅ Added
        'vat_amount' => 'decimal:2',
        'selling_price' => 'decimal:2'
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

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inven_code', 'inven_code');
    }

    /**
     * Get line total before discount
     */
    public function getLineTotalAttribute()
    {
        // Use selling_price from receipt_item if available, otherwise from product
        $unitPrice = $this->selling_price ?? $this->product->selling_price;
        return $this->item_quantity * $unitPrice;
    }

    /**
     * Get item discount amount
     * ✅ Now returns the stored calculated amount
     */
    public function getItemDiscountAmountAttribute()
    {
        // Return stored amount if available
        if (isset($this->attributes['item_discount_amount'])) {
            return $this->attributes['item_discount_amount'];
        }
        
        // Fallback calculation for old records
        $lineTotal = $this->line_total;
        
        if ($this->item_discount_type == 'percent') {
            return $lineTotal * ($this->item_discount_value / 100);
        }
        
        return $this->item_discount_value ??  0;
    }

    /**
     * Get total amount after item discount
     */
    public function getTotalAmountAttribute()
    {
        return $this->line_total - $this->item_discount_amount;
    }

    /**
     * Get unit price after discount
     */
    public function getUnitPriceAfterDiscountAttribute()
    {
        if ($this->item_quantity == 0) {
            return 0;
        }
        
        return $this->total_amount / $this->item_quantity;
    }

    /**
     * Get batch information
     */
    public function getBatchInfoAttribute()
    {
        if ($this->inven_code) {
            return [
                'inven_code' => $this->inven_code,
                'batch_number' => $this->batch_number,
                'selling_price' => $this->selling_price
            ];
        }
        
        return null;
    }

    /**
     * ✅ Check if this item has a discount applied
     */
    public function hasDiscount()
    {
        return ($this->item_discount_amount ??  0) > 0;
    }

    /**
     * ✅ Get discount percentage (calculated from stored amount)
     */
    public function getDiscountPercentageAttribute()
    {
        if ($this->line_total == 0) {
            return 0;
        }
        
        return ($this->item_discount_amount / $this->line_total) * 100;
    }

    /**
     * ✅ Get formatted discount display
     * Example: "₱5.00 (5%)" or "₱10.00"
     */
    public function getFormattedDiscountAttribute()
    {
        if (! $this->hasDiscount()) {
            return null;
        }
        
        $amount = '₱' . number_format($this->item_discount_amount, 2);
        
        if ($this->item_discount_type == 'percent' && $this->item_discount_value > 0) {
            return $amount . ' (' . number_format($this->item_discount_value, 2) . '%)';
        }
        
        return $amount;
    }
}