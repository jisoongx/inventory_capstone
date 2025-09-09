<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\ReceiptItem;
use App\Models\Inventory;

class StoreController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date');
   
        $query = DB::table('receipt')
            ->join('receipt_item', 'receipt.receipt_id', '=', 'receipt_item.receipt_id')
            ->join('products', 'receipt_item.prod_code', '=', 'products.prod_code')
            ->select(
                'receipt.receipt_id',
                'receipt.receipt_date',
                DB::raw('SUM(receipt_item.item_quantity) as items_quantity'),
                DB::raw('SUM(receipt_item.item_quantity * products.cost_price) as total_amount')
            )
            ->groupBy('receipt.receipt_id', 'receipt.receipt_date')
            ->orderBy('receipt.receipt_date', 'desc');
   
        if ($date) {
            $query->whereDate('receipt.receipt_date', $date);
        }
   
        $transactions = $query->get();
   
        return view('store_transactions', [
            'transactions' => $transactions,
            'date' => $date,
        ]);
    }

    public function searchProduct(Request $request)
    {
        $request->validate([
            'prod_code' => 'required|string',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::where('prod_code', $request->prod_code)
                         ->orWhere('barcode', $request->prod_code)
                         ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found. Please check the product code or barcode.'
            ], 404);
        }

        // Get total stock from inventory table
        $totalStock = Inventory::where('prod_code', $product->prod_code)
                              ->sum('stock');

        if ($totalStock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient stock. Available quantity: {$totalStock}"
            ], 400);
        }

        // Check if requesting quantity will result in low stock warning
        $remainingAfterSale = $totalStock - $request->quantity;
        $lowStockWarning = $remainingAfterSale <= $product->stock_limit;

        return response()->json([
            'success' => true,
            'product' => [
                'prod_code' => $product->prod_code,
                'barcode' => $product->barcode,
                'name' => $product->name,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
                'available_quantity' => $totalStock,
                'stock_limit' => $product->stock_limit,
                'description' => $product->description
            ],
            'requested_quantity' => $request->quantity,
            'total_amount' => $product->cost_price * $request->quantity,
            'low_stock_warning' => $lowStockWarning,
            'remaining_after_sale' => $remainingAfterSale
        ]);
    }

    public function startTransaction(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.prod_code' => 'required|exists:products,prod_code',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        // Additional validation: Check if all items have sufficient inventory stock
        foreach ($request->items as $item) {
            $totalStock = Inventory::where('prod_code', $item['prod_code'])
                                 ->sum('stock');
            
            if ($totalStock < $item['quantity']) {
                $product = Product::find($item['prod_code']);
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock for {$product->name}. Available: {$totalStock}"
                ], 400);
            }
        }

        // Store items in session for the transaction interface
        session()->put('transaction_items', $request->items);
        
        return response()->json([
            'success' => true,
            'redirect_url' => route('store_start_transaction')
        ]);
    }

    public function showStartTransaction()
    {
        $items = session()->get('transaction_items', []);
        
        if (empty($items)) {
            return redirect()->route('store_transactions')->with('error', 'No items found for transaction.');
        }

        $transactionItems = [];
        $totalAmount = 0;
        $totalQuantity = 0;

        foreach ($items as $item) {
            $product = Product::find($item['prod_code']);
            if ($product) {
                // Get current stock from inventory
                $currentStock = Inventory::where('prod_code', $item['prod_code'])
                                       ->sum('stock');
                
                $itemTotal = $product->cost_price * $item['quantity'];
                $transactionItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'amount' => $itemTotal,
                    'current_stock' => $currentStock
                ];
                $totalAmount += $itemTotal;
                $totalQuantity += $item['quantity'];
            }
        }

        // Get user firstname based on authentication guard
        $user_firstname = null;
        
        if (Auth::guard('owner')->check()) {
            $user_firstname = Auth::guard('owner')->user()->firstname;
        } elseif (Auth::guard('staff')->check()) {
            $user_firstname = Auth::guard('staff')->user()->firstname;
        }

        return view('store_start_transaction', [
            'items' => $transactionItems,
            'total_amount' => $totalAmount,
            'total_quantity' => $totalQuantity,
            'receipt_no' => $this->generateReceiptNumber(),
            'user_firstname' => $user_firstname
        ]);
    }

    public function updateTransactionItems(Request $request)
    {
    $request->validate([
        'items' => 'required|array',
        'items.*.prod_code' => 'required|exists:products,prod_code',
        'items.*.quantity' => 'required|integer|min:1'
    ]);

    // Update session with current items
    session()->put('transaction_items', $request->items);
    
    return response()->json(['success' => true]);
    }

    public function processPayment(Request $request)
    {
    $request->validate([
        'payment_method' => 'required|string',
        'amount_received' => 'required|numeric|min:0',
        'items' => 'sometimes|array', // Make it optional - can come from request or session
        'items.*.prod_code' => 'required_with:items|exists:products,prod_code',
        'items.*.quantity' => 'required_with:items|integer|min:1'
    ]);

    // Use items from request if provided, otherwise fall back to session
    $items = $request->has('items') ? $request->items : session()->get('transaction_items', []);
    
    if (empty($items)) {
        return response()->json([
            'success' => false,
            'message' => 'No items found for transaction.'
        ], 400);
    }

    DB::beginTransaction();

    try {
        // Get proper user IDs based on authentication guards
        $owner_id = null;
        $staff_id = null;
        
        if (Auth::guard('owner')->check()) {
            $owner_id = Auth::guard('owner')->user()->owner_id;
        } elseif (Auth::guard('staff')->check()) {
            $staff_id = Auth::guard('staff')->user()->staff_id;
            $owner_id = Auth::guard('staff')->user()->owner_id;
        }

        // Create receipt
        $receipt = Receipt::create([
            'receipt_date' => now(),
            'owner_id' => $owner_id ?? 1,
            'staff_id' => $staff_id
        ]);

        $totalAmount = 0;
        $lowStockProducts = [];

        // Create receipt items and update inventory stock
        foreach ($items as $item) {
            $product = Product::find($item['prod_code']);
            
            if (!$product) {
                throw new \Exception("Product not found: " . $item['prod_code']);
            }

            // Check current total available stock in inventory
            $totalStock = Inventory::where('prod_code', $item['prod_code'])
                                 ->sum('stock');
            
            if ($totalStock < $item['quantity']) {
                throw new \Exception("Insufficient stock for product: " . $product->name . ". Available: {$totalStock}");
            }

            // Create receipt item
            ReceiptItem::create([
                'item_quantity' => $item['quantity'],
                'prod_code' => $item['prod_code'],
                'receipt_id' => $receipt->receipt_id
            ]);

            // Update inventory stock using FIFO method
            $this->decrementInventoryStock($item['prod_code'], $item['quantity']);
            
            // Check if product is now low stock
            $remainingStock = Inventory::where('prod_code', $item['prod_code'])->sum('stock');
            if ($remainingStock <= $product->stock_limit) {
                $lowStockProducts[] = [
                    'name' => $product->name,
                    'remaining_stock' => $remainingStock,
                    'stock_limit' => $product->stock_limit
                ];
            }
            
            $totalAmount += $product->cost_price * $item['quantity'];
        }

        // Clear session
        session()->forget('transaction_items');

        DB::commit();

        $response = [
            'success' => true,
            'message' => 'Transaction completed successfully!',
            'receipt_id' => $receipt->receipt_id,
            'total_amount' => $totalAmount,
            'amount_received' => $request->amount_received,
            'change' => $request->amount_received - $totalAmount
        ];

        // Add low stock warning if any
        if (!empty($lowStockProducts)) {
            $response['low_stock_warning'] = $lowStockProducts;
        }

        return response()->json($response);

    } catch (\Exception $e) {
        DB::rollBack();
        
        return response()->json([
            'success' => false,
            'message' => 'Transaction failed: ' . $e->getMessage()
        ], 500);
    }
    }

    /**
     * Decrement inventory stock using FIFO method (First In, First Out)
     * This ensures older inventory is sold first
     */
    private function decrementInventoryStock($prod_code, $quantity)
{
    $remainingQuantity = $quantity;
    
    // Get inventory records ordered by date_added (FIFO) and then by inven_code for consistency
    $inventoryItems = Inventory::where('prod_code', $prod_code)
                             ->where('stock', '>', 0)
                             ->orderBy('date_added', 'asc')
                             ->orderBy('inven_code', 'asc')
                             ->get();

    if ($inventoryItems->isEmpty()) {
        throw new \Exception("No inventory records found for product code: {$prod_code}");
    }

    foreach ($inventoryItems as $item) {
        if ($remainingQuantity <= 0) {
            break;
        }

        if ($item->stock >= $remainingQuantity) {
            // This inventory item has enough stock to fulfill the remaining quantity
            $item->decrement('stock', $remainingQuantity);
            $remainingQuantity = 0;
        } else {
            // Use all stock from this item and continue to next
            $remainingQuantity -= $item->stock;
            $item->update(['stock' => 0]);
        }
    }

    if ($remainingQuantity > 0) {
        throw new \Exception("Not enough inventory stock available. Missing: {$remainingQuantity} units");
    }
}

    private function generateReceiptNumber()
    {
        $lastReceipt = Receipt::orderBy('receipt_id', 'desc')->first();
        return $lastReceipt ? $lastReceipt->receipt_id + 1 : 1;
    }
}