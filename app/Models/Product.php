<? php

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
        'vat_category',  // ✅ Add this line
        'stock_limit',
        'description',
        'owner_id',
        'staff_id',
        'category_id',
        'unit_id',
        'prod_image'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_limit' => 'integer',
        'vat_category' => 'string'  // ✅ Add this line (optional but good practice)
    ];

    // Relationships
    public function receiptItems()
    {
        return $this->hasMany(ReceiptItem::class, 'prod_code', 'prod_code');
    }

    // Relationship with Inventory
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'prod_code', 'prod_code');
    }

    // Helper method to get total stock from inventory
    public function getTotalStockAttribute()
    {
        return $this->inventories()->sum('stock');
    }

    // Helper method to check if stock is below limit
    public function isLowStockAttribute()
    {
        return $this->total_stock <= $this->stock_limit;
    }
}