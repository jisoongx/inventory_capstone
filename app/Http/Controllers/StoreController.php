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
    // Helper method to get current owner ID
    private function getCurrentOwnerId()
    {
        if (Auth::guard('owner')->check()) {
            return Auth::guard('owner')->user()->owner_id;
        } elseif (Auth::guard('staff')->check()) {
            return Auth::guard('staff')->user()->owner_id;
        }
        
        return null;
    }

    public function index(Request $request)
    {
        $ownerId = $this->getCurrentOwnerId();
        
        if (!$ownerId) {
            abort(403, 'Unauthorized access');
        }

        $date = $request->get('date');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
   
        $query = DB::table('receipt')
            ->join('receipt_item', 'receipt.receipt_id', '=', 'receipt_item.receipt_id')
            ->join('products', 'receipt_item.prod_code', '=', 'products.prod_code')
            ->select(
                'receipt.receipt_id',
                'receipt.receipt_date',
                DB::raw('SUM(receipt_item.item_quantity) as items_quantity'),
                DB::raw('SUM(receipt_item.item_quantity * products.selling_price) as total_amount')
            )
            ->where('receipt.owner_id', $ownerId)
            ->groupBy('receipt.receipt_id', 'receipt.receipt_date')
            ->orderBy('receipt.receipt_date', 'desc');
   
        if ($date) {
            $query->whereDate('receipt.receipt_date', $date);
        }
        
        if ($startDate && $endDate) {
            $query->whereBetween('receipt.receipt_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay()
            ]);
        }
   
        $transactions = $query->get();
   
        return view('store_transactions', [
            'transactions' => $transactions,
            'date' => $date,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    // View receipt details
    public function getReceiptDetails($receiptId)
    {
        try {
            $ownerId = $this->getCurrentOwnerId();
            
            if (!$ownerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $receipt = DB::table('receipt')
                ->leftJoin('owners', 'receipt.owner_id', '=', 'owners.owner_id')
                ->leftJoin('staff', 'receipt.staff_id', '=', 'staff.staff_id')
                ->select('receipt.*', 'owners.firstname as owner_name', 'staff.firstname as staff_name')
                ->where('receipt.receipt_id', $receiptId)
                ->where('receipt.owner_id', $ownerId)
                ->first();

            if (!$receipt) {
                return response()->json([
                    'success' => false,
                    'message' => 'Receipt not found or access denied'
                ], 404);
            }

            $items = DB::table('receipt_item')
                ->join('products', 'receipt_item.prod_code', '=', 'products.prod_code')
                ->select('receipt_item.*', 'products.name as product_name', 'products.selling_price')
                ->where('receipt_item.receipt_id', $receiptId)
                ->get();

            $storeInfo = DB::table('owners')
                ->select('store_name', 'store_address', 'contact')
                ->where('owner_id', $ownerId)
                ->first();

            return response()->json([
                'success' => true,
                'receipt' => $receipt,
                'items' => $items,
                'store_info' => $storeInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    // Show the kiosk-style transaction interface
    public function showKioskTransaction()
    {
        session()->forget('transaction_items');
        
        $user_firstname = null;
        $owner_id = $this->getCurrentOwnerId();
        
        if (!$owner_id) {
            abort(403, 'Unauthorized access');
        }
        
        if (Auth::guard('owner')->check()) {
            $user_firstname = Auth::guard('owner')->user()->firstname;
        } elseif (Auth::guard('staff')->check()) {
            $user_firstname = Auth::guard('staff')->user()->firstname;
        }
        
        // Get store information
        $store_info = DB::table('owners')
            ->select('store_name', 'store_address', 'contact')
            ->where('owner_id', $owner_id)
            ->first();

        return view('store_start_transaction', [
            'receipt_no' => $this->generateReceiptNumber($owner_id),
            'user_firstname' => $user_firstname,
            'store_info' => $store_info,
            'expired' => false // Add your subscription check logic here
        ]);
    }

    // Show payment processor page
    public function showPaymentProcessor()
    {
        $owner_id = $this->getCurrentOwnerId();
        
        if (!$owner_id) {
            abort(403, 'Unauthorized access');
        }
        
        // Check if cart has items
        $items = session()->get('transaction_items', []);
        
        if (empty($items)) {
            return redirect()->route('store_start_transaction')
                ->with('error', 'Cart is empty. Please add items first.');
        }
        
        $user_firstname = null;
        
        if (Auth::guard('owner')->check()) {
            $user_firstname = Auth::guard('owner')->user()->firstname;
        } elseif (Auth::guard('staff')->check()) {
            $user_firstname = Auth::guard('staff')->user()->firstname;
        }
        
        // Get store information
        $store_info = DB::table('owners')
            ->select('store_name', 'store_address', 'contact')
            ->where('owner_id', $owner_id)
            ->first();
        
        // Get receipt number
        $receipt_no = $this->generateReceiptNumber($owner_id);
        
        return view('store_payment_processor', [
            'receipt_no' => $receipt_no,
            'user_firstname' => $user_firstname,
            'store_info' => $store_info,
            'expired' => false // Add your subscription check logic here
        ]);
    }

    // Get all categories for the dropdown filter
    public function getCategories()
    {
        try {
            $ownerId = $this->getCurrentOwnerId();
            
            if (!$ownerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $categories = DB::table('categories')
                ->select('category_id', 'category')
                ->where('owner_id', $ownerId)
                ->orderBy('category')
                ->get();
            
            return response()->json([
                'success' => true,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading categories: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get products with inventory for kiosk display
    public function getKioskProducts(Request $request)
    {
        try {
            $ownerId = $this->getCurrentOwnerId();
            
            if (!$ownerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $categoryId = $request->get('category_id');
            $search = $request->get('search');
            
            $query = DB::table('products')
                ->join('categories', 'products.category_id', '=', 'categories.category_id')
                ->leftJoin('inventory', function($join) use ($ownerId) {
                    $join->on('products.prod_code', '=', 'inventory.prod_code')
                         ->where('inventory.owner_id', '=', $ownerId);
                })
                ->select(
                    'products.prod_code',
                    'products.name',
                    'products.selling_price',
                    'products.stock_limit',
                    'products.barcode',
                    'products.prod_image',
                    'products.category_id',
                    'categories.category as category_name',
                    DB::raw('COALESCE(SUM(inventory.stock), 0) as stock')
                )
                ->where('products.owner_id', $ownerId)
                ->groupBy(
                    'products.prod_code',
                    'products.name',
                    'products.selling_price',
                    'products.stock_limit',
                    'products.barcode',
                    'products.prod_image',
                    'products.category_id',
                    'categories.category'
                )
                ->orderBy('products.name');

            if ($categoryId) {
                $query->where('products.category_id', $categoryId);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('products.name', 'LIKE', "%{$search}%")
                      ->orWhere('products.barcode', 'LIKE', "%{$search}%");
                });
            }

            $products = $query->get();

            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading products: ' . $e->getMessage()
            ], 500);
        }
    }

    // Add item to kiosk cart
    public function addToKioskCart(Request $request)
    {
        $request->validate([
            'prod_code' => 'required|exists:products,prod_code',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            $ownerId = $this->getCurrentOwnerId();
            
            if (!$ownerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
            
            // Verify product belongs to this owner
            $product = Product::where('prod_code', $request->prod_code)
                              ->where('owner_id', $ownerId)
                              ->first();
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found or access denied.'
                ], 404);
            }

            $totalStock = Inventory::where('prod_code', $product->prod_code)
                                   ->where('owner_id', $ownerId)
                                   ->sum('stock');

            $currentItems = session()->get('transaction_items', []);
            $currentQuantity = 0;
            
            foreach ($currentItems as $item) {
                if ($item['prod_code'] == $request->prod_code) {
                    $currentQuantity = $item['quantity'];
                    break;
                }
            }

            $newTotalQuantity = $currentQuantity + $request->quantity;

            if ($totalStock < $newTotalQuantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Insufficient stock. Available: {$totalStock}, In cart: {$currentQuantity}"
                ], 400);
            }

            $itemFound = false;
            foreach ($currentItems as &$item) {
                if ($item['prod_code'] == $request->prod_code) {
                    $item['quantity'] = $newTotalQuantity;
                    $itemFound = true;
                    break;
                }
            }

            if (!$itemFound) {
                $currentItems[] = [
                    'prod_code' => $request->prod_code,
                    'quantity' => $request->quantity
                ];
            }

            session()->put('transaction_items', $currentItems);

            $cartItems = $this->getFormattedCartItems($currentItems, $ownerId);

            return response()->json([
                'success' => true,
                'message' => $product->name . ' added to cart successfully!',
                'cart_items' => $cartItems['items'],
                'cart_summary' => [
                    'total_quantity' => $cartItems['total_quantity'],
                    'total_amount' => $cartItems['total_amount']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding item: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update cart item quantity
    public function updateCartItem(Request $request)
    {
        $request->validate([
            'prod_code' => 'required|exists:products,prod_code',
            'quantity' => 'required|integer|min:0'
        ]);

        try {
            $ownerId = $this->getCurrentOwnerId();
            
            if (!$ownerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $currentItems = session()->get('transaction_items', []);
            
            if ($request->quantity == 0) {
                $currentItems = array_filter($currentItems, function($item) use ($request) {
                    return $item['prod_code'] != $request->prod_code;
                });
                $currentItems = array_values($currentItems);
            } else {
                $totalStock = Inventory::where('prod_code', $request->prod_code)
                                       ->where('owner_id', $ownerId)
                                       ->sum('stock');
                
                if ($totalStock < $request->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock. Available: {$totalStock}"
                    ], 400);
                }

                foreach ($currentItems as &$item) {
                    if ($item['prod_code'] == $request->prod_code) {
                        $item['quantity'] = $request->quantity;
                        break;
                    }
                }
            }

            session()->put('transaction_items', $currentItems);
            
            $cartItems = $this->getFormattedCartItems($currentItems, $ownerId);

            return response()->json([
                'success' => true,
                'cart_items' => $cartItems['items'],
                'cart_summary' => [
                    'total_quantity' => $cartItems['total_quantity'],
                    'total_amount' => $cartItems['total_amount']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating cart: ' . $e->getMessage()
            ], 500);
        }
    }

    // Remove item from cart with reason
    public function removeCartItem(Request $request)
    {
        $request->validate([
            'prod_code' => 'required|exists:products,prod_code',
            'reason' => 'required|in:return,damage,cancel'
        ]);

        try {
            $currentItems = session()->get('transaction_items', []);
            
            $currentItems = array_filter($currentItems, function($item) use ($request) {
                return $item['prod_code'] != $request->prod_code;
            });
            $currentItems = array_values($currentItems);

            session()->put('transaction_items', $currentItems);
            
            $ownerId = $this->getCurrentOwnerId();
            $cartItems = $this->getFormattedCartItems($currentItems, $ownerId);

            return response()->json([
                'success' => true,
                'message' => 'Item removed successfully.',
                'cart_items' => $cartItems['items'],
                'cart_summary' => [
                    'total_quantity' => $cartItems['total_quantity'],
                    'total_amount' => $cartItems['total_amount']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing item: ' . $e->getMessage()
            ], 500);
        }
    }

    // Get current cart items
    public function getCartItems()
    {
        try {
            $ownerId = $this->getCurrentOwnerId();
            $currentItems = session()->get('transaction_items', []);
            $cartItems = $this->getFormattedCartItems($currentItems, $ownerId);

            return response()->json([
                'success' => true,
                'cart_items' => $cartItems['items'],
                'cart_summary' => [
                    'total_quantity' => $cartItems['total_quantity'],
                    'total_amount' => $cartItems['total_amount']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting cart items: ' . $e->getMessage()
            ], 500);
        }
    }

    // Process barcode search
    public function processBarcodeSearch(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        try {
            $ownerId = $this->getCurrentOwnerId();
            
            if (!$ownerId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            $product = Product::where('barcode', $request->barcode)
                              ->where('owner_id', $ownerId)
                              ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found with this barcode.'
                ], 404);
            }

            $totalStock = Inventory::where('prod_code', $product->prod_code)
                                   ->where('owner_id', $ownerId)
                                   ->sum('stock');

            return response()->json([
                'success' => true,
                'product' => [
                    'prod_code' => $product->prod_code,
                    'name' => $product->name,
                    'selling_price' => $product->selling_price,
                    'stock_limit' => $product->stock_limit,
                    'barcode' => $product->barcode,
                    'prod_image' => $product->prod_image,
                    'category_id' => $product->category_id,
                    'stock' => $totalStock
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing barcode: ' . $e->getMessage()
            ], 500);
        }
    }

    // Process payment
    public function processPayment(Request $request)
{
    // Validate incoming request
    $request->validate([
        'payment_method' => 'required|string',
        'amount_paid' => 'required|numeric|min:0',
        'receipt_discount_type' => 'nullable|in:percent,amount', // Changed from 'fixed' to 'amount'
        'receipt_discount_value' => 'nullable|numeric|min:0',
        'vat_enabled' => 'nullable|boolean',
        'vat_rate' => 'nullable|numeric|min:0|max:100',
        'item_discounts' => 'nullable|array'
    ]);

    $items = session()->get('transaction_items', []);
    
    if (empty($items)) {
        return response()->json([
            'success' => false,
            'message' => 'No items found for transaction.'
        ], 400);
    }

    DB::beginTransaction();

    try {
        $owner_id = $this->getCurrentOwnerId();
        $staff_id = null;
        
        if (!$owner_id) {
            throw new \Exception('Unauthorized access');
        }
        
        if (Auth::guard('staff')->check()) {
            $staff_id = Auth::guard('staff')->user()->staff_id;
        }

        // Get discount and VAT values - Convert 'fixed' to 'amount' for database
        $receiptDiscountType = $request->input('receipt_discount_type', 'percent');
        if ($receiptDiscountType === 'fixed') {
            $receiptDiscountType = 'amount';
        }
        
        $receiptDiscountValue = $request->input('receipt_discount_value', 0);
        $vatEnabled = $request->input('vat_enabled', false);
        $vatRate = $request->input('vat_rate', 0);
        $itemDiscounts = $request->input('item_discounts', []);

        $lowStockProducts = [];
        $receiptItems = [];
        $subtotal = 0;

        // Validate stock and calculate subtotal
        foreach ($items as $item) {
            $product = Product::where('prod_code', $item['prod_code'])
                              ->where('owner_id', $owner_id)
                              ->first();
            
            if (!$product) {
                throw new \Exception("Product not found or access denied: " . $item['prod_code']);
            }

            $totalStock = Inventory::where('prod_code', $item['prod_code'])
                                   ->where('owner_id', $owner_id)
                                   ->sum('stock');
            
            if ($totalStock < $item['quantity']) {
                throw new \Exception("Insufficient stock for product: " . $product->name . ". Available: {$totalStock}");
            }

            $itemTotal = $product->selling_price * $item['quantity'];
            $subtotal += $itemTotal;

            $receiptItems[] = [
                'product' => $product,
                'quantity' => $item['quantity'],
                'amount' => $itemTotal
            ];
        }

        // Calculate total with discounts
        $totalItemDiscounts = 0;
        foreach ($items as $item) {
            $product = Product::where('prod_code', $item['prod_code'])->first();
            $itemTotal = $product->selling_price * $item['quantity'];
            
            if (isset($itemDiscounts[$item['prod_code']])) {
                $discount = $itemDiscounts[$item['prod_code']];
                $discountType = $discount['type'] === 'fixed' ? 'amount' : $discount['type'];
                
                if ($discountType === 'percent') {
                    $totalItemDiscounts += $itemTotal * ($discount['value'] / 100);
                } else {
                    $totalItemDiscounts += $discount['value'];
                }
            }
        }

        $afterItemDiscounts = $subtotal - $totalItemDiscounts;

        // Apply receipt-level discount
        $receiptDiscountAmount = 0;
        if ($receiptDiscountType === 'percent') {
            $receiptDiscountAmount = $afterItemDiscounts * ($receiptDiscountValue / 100);
        } else {
            $receiptDiscountAmount = $receiptDiscountValue;
        }

        $afterReceiptDiscount = $afterItemDiscounts - $receiptDiscountAmount;

        // Calculate VAT
        $vatAmount = 0;
        if ($vatEnabled) {
            $vatAmount = $afterReceiptDiscount * ($vatRate / 100);
        }

        $totalAmount = $afterReceiptDiscount + $vatAmount;

        // Validate amount paid
        if ($request->amount_paid < $totalAmount) {
            throw new \Exception("Amount paid (₱" . number_format($request->amount_paid, 2) . ") is less than total amount (₱" . number_format($totalAmount, 2) . ")");
        }

        // Create receipt
        $receipt = Receipt::create([
            'receipt_date' => now(),
            'owner_id' => $owner_id,
            'staff_id' => $staff_id,
            'amount_paid' => $request->amount_paid,
            'discount_type' => $receiptDiscountType, // Now correctly 'percent' or 'amount'
            'discount_value' => $receiptDiscountValue
        ]);

        // Process each item
        foreach ($items as $item) {
            $product = Product::where('prod_code', $item['prod_code'])
                              ->where('owner_id', $owner_id)
                              ->first();

            // Get item discount - Convert 'fixed' to 'amount'
            $itemDiscountType = 'percent';
            $itemDiscountValue = 0;
            
            if (isset($itemDiscounts[$item['prod_code']])) {
                $itemDiscountType = $itemDiscounts[$item['prod_code']]['type'];
                if ($itemDiscountType === 'fixed') {
                    $itemDiscountType = 'amount';
                }
                $itemDiscountValue = $itemDiscounts[$item['prod_code']]['value'];
            }

            // Calculate VAT per item (proportional)
            $itemTotal = $product->selling_price * $item['quantity'];
            $itemProportion = $subtotal > 0 ? $itemTotal / $subtotal : 0;
            $itemVatAmount = $vatEnabled ? $vatAmount * $itemProportion : 0;

            // Create receipt item
            ReceiptItem::create([
                'item_quantity' => $item['quantity'],
                'prod_code' => $item['prod_code'],
                'receipt_id' => $receipt->receipt_id,
                'item_discount_type' => $itemDiscountType, // Now correctly 'percent' or 'amount'
                'item_discount_value' => $itemDiscountValue,
                'vat_amount' => $itemVatAmount
            ]);

            // Decrement inventory
            $this->decrementInventoryStock($item['prod_code'], $item['quantity'], $owner_id);
            
            // Check for low stock
            $remainingStock = Inventory::where('prod_code', $item['prod_code'])
                                       ->where('owner_id', $owner_id)
                                       ->sum('stock');
                                       
            if ($remainingStock <= $product->stock_limit) {
                $lowStockProducts[] = [
                    'name' => $product->name,
                    'remaining_stock' => $remainingStock,
                    'stock_limit' => $product->stock_limit
                ];
            }
        }

        // Clear session
        session()->forget('transaction_items');

        DB::commit();

        $response = [
            'success' => true,
            'message' => 'Transaction completed successfully!',
            'receipt_id' => $receipt->receipt_id,
            'subtotal' => $subtotal,
            'total_item_discounts' => $totalItemDiscounts,
            'receipt_discount_amount' => $receiptDiscountAmount,
            'vat_amount' => $vatAmount,
            'total_amount' => $totalAmount,
            'amount_paid' => $request->amount_paid,
            'change' => $request->amount_paid - $totalAmount,
            'receipt_items' => $receiptItems,
            'total_quantity' => array_sum(array_column($items, 'quantity'))
        ];

        if (!empty($lowStockProducts)) {
            $response['low_stock_warning'] = $lowStockProducts;
        }

        return response()->json($response);

    } catch (\Exception $e) {
        DB::rollBack();
        
        // Log the error for debugging
        \Log::error('Payment processing error: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
            'request_data' => $request->all()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Transaction failed: ' . $e->getMessage()
        ], 500);
    }
}

    // Helper method to format cart items
    private function getFormattedCartItems($items, $ownerId)
    {
        $formattedItems = [];
        $totalAmount = 0;
        $totalQuantity = 0;

        foreach ($items as $item) {
            $product = Product::where('prod_code', $item['prod_code'])
                              ->where('owner_id', $ownerId)
                              ->first();
                              
            if ($product) {
                $currentStock = Inventory::where('prod_code', $item['prod_code'])
                                         ->where('owner_id', $ownerId)
                                         ->sum('stock');
                $itemTotal = $product->selling_price * $item['quantity'];
                
                $formattedItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'amount' => $itemTotal,
                    'current_stock' => $currentStock
                ];
                
                $totalAmount += $itemTotal;
                $totalQuantity += $item['quantity'];
            }
        }

        return [
            'items' => $formattedItems,
            'total_amount' => $totalAmount,
            'total_quantity' => $totalQuantity
        ];
    }

    private function decrementInventoryStock($prod_code, $quantity, $ownerId)
    {
        $remainingQuantity = $quantity;
        
        $inventoryItems = Inventory::where('prod_code', $prod_code)
                                   ->where('owner_id', $ownerId)
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
                $item->decrement('stock', $remainingQuantity);
                $remainingQuantity = 0;
            } else {
                $remainingQuantity -= $item->stock;
                $item->update(['stock' => 0]);
            }
        }

        if ($remainingQuantity > 0) {
            throw new \Exception("Not enough inventory stock available. Missing: {$remainingQuantity} units");
        }
    }

    private function generateReceiptNumber($ownerId)
    {
        $lastReceipt = Receipt::where('owner_id', $ownerId)
                              ->orderBy('receipt_id', 'desc')
                              ->first();
        return $lastReceipt ? $lastReceipt->receipt_id + 1 : 1;
    }

    public function showReports(Request $request)
    {
        $ownerId = $this->getCurrentOwnerId();
        
        if (!$ownerId) {
            abort(403, 'Unauthorized access');
        }
        
        // Get filter parameters for transactions
        $date = $request->get('date');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        
        // Build transactions query
        $transactionsQuery = DB::table('receipt')
            ->join('receipt_item', 'receipt.receipt_id', '=', 'receipt_item.receipt_id')
            ->join('products', 'receipt_item.prod_code', '=', 'products.prod_code')
            ->select(
                'receipt.receipt_id',
                'receipt.receipt_date',
                DB::raw('SUM(receipt_item.item_quantity) as items_quantity'),
                DB::raw('SUM(receipt_item.item_quantity * products.selling_price) as total_amount')
            )
            ->where('receipt.owner_id', $ownerId)
            ->groupBy('receipt.receipt_id', 'receipt.receipt_date')
            ->orderBy('receipt.receipt_date', 'desc');
        
        // Apply date filters
        if ($date) {
            $transactionsQuery->whereDate('receipt.receipt_date', $date);
        }
        
        if ($start_date && $end_date) {
            $transactionsQuery->whereBetween('receipt.receipt_date', [
                Carbon::parse($start_date)->startOfDay(),
                Carbon::parse($end_date)->endOfDay()
            ]);
        }
        
        $transactions = $transactionsQuery->get();
        
        // Get data for Sales by Category tab
        $years = DB::table('receipt')
            ->select(DB::raw('DISTINCT YEAR(receipt_date) as year'))
            ->where('owner_id', $ownerId)
            ->orderBy('year', 'desc')
            ->get();
        
        $monthNames = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];
        
        // Placeholder for sales by category data
        $sbc = collect();
        
        // Get data for Peak Hours tab
        $dateChoice = $request->get('dateChoice', now()->toDateString());
        $peak = DB::table('receipt')
            ->select(
                DB::raw('DAYNAME(receipt_date) as dayName'),
                DB::raw('HOUR(receipt_date) as hour'),
                DB::raw('CONCAT(HOUR(receipt_date), ":00 - ", HOUR(receipt_date) + 1, ":00") as time_slot'),
                DB::raw('COUNT(*) as transactions'),
                DB::raw('SUM((SELECT SUM(ri.item_quantity * p.selling_price) 
                             FROM receipt_item ri 
                             JOIN products p ON ri.prod_code = p.prod_code 
                             WHERE ri.receipt_id = receipt.receipt_id)) as sales'),
                DB::raw('AVG((SELECT SUM(ri.item_quantity * p.selling_price) 
                             FROM receipt_item ri 
                             JOIN products p ON ri.prod_code = p.prod_code 
                             WHERE ri.receipt_id = receipt.receipt_id)) as avg_value')
            )
            ->where('owner_id', $ownerId)
            ->whereDate('receipt_date', $dateChoice)
            ->groupBy('dayName', 'hour', 'time_slot')
            ->orderBy('hour')
            ->get();
        
        return view('dashboards.owner.report-sales-performance', compact(
            'transactions',
            'date',
            'start_date',
            'end_date',
            'years',
            'monthNames',
            'sbc',
            'peak',
            'dateChoice'
        ));
    }
}