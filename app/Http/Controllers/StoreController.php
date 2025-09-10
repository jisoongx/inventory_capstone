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
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
   
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

    // Show the kiosk-style transaction interface
    public function showKioskTransaction()
    {
        session()->forget('transaction_items');
        
        $user_firstname = null;
        
        if (Auth::guard('owner')->check()) {
            $user_firstname = Auth::guard('owner')->user()->firstname;
        } elseif (Auth::guard('staff')->check()) {
            $user_firstname = Auth::guard('staff')->user()->firstname;
        }

        return view('store_start_transaction', [
            'receipt_no' => $this->generateReceiptNumber(),
            'user_firstname' => $user_firstname
        ]);
    }

    // Get all categories for the dropdown filter
    public function getCategories()
    {
        try {
            $categories = DB::table('categories')
                ->select('category_id', 'category')
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
            $categoryId = $request->get('category_id');
            $search = $request->get('search');
            
            $query = DB::table('products')
                ->join('categories', 'products.category_id', '=', 'categories.category_id')
                ->leftJoin('inventory', 'products.prod_code', '=', 'inventory.prod_code')
                ->select(
                    'products.prod_code',
                    'products.name',
                    'products.cost_price',
                    'products.stock_limit',
                    'products.barcode',
                    'products.prod_image',
                    'products.category_id',
                    'categories.category as category_name',
                    DB::raw('COALESCE(SUM(inventory.stock), 0) as stock')
                )
                ->groupBy(
                    'products.prod_code',
                    'products.name',
                    'products.cost_price',
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
            $product = Product::find($request->prod_code);
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.'
                ], 404);
            }

            $totalStock = Inventory::where('prod_code', $product->prod_code)->sum('stock');

            $currentItems = session()->get('transaction_items', []);
            $currentQuantity = 0;
            
            // Check if item already exists in cart
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

            // Update or add item to session
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

            // Prepare response data
            $cartItems = $this->getFormattedCartItems($currentItems);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully!',
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
            $currentItems = session()->get('transaction_items', []);
            
            if ($request->quantity == 0) {
                // Remove item from cart
                $currentItems = array_filter($currentItems, function($item) use ($request) {
                    return $item['prod_code'] != $request->prod_code;
                });
                $currentItems = array_values($currentItems); // Re-index array
            } else {
                // Check stock availability
                $totalStock = Inventory::where('prod_code', $request->prod_code)->sum('stock');
                
                if ($totalStock < $request->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock. Available: {$totalStock}"
                    ], 400);
                }

                // Update quantity
                foreach ($currentItems as &$item) {
                    if ($item['prod_code'] == $request->prod_code) {
                        $item['quantity'] = $request->quantity;
                        break;
                    }
                }
            }

            session()->put('transaction_items', $currentItems);
            
            $cartItems = $this->getFormattedCartItems($currentItems);

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
            
            $cartItems = $this->getFormattedCartItems($currentItems);

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

    // Clear all cart items
    public function clearKioskCart()
    {
        session()->forget('transaction_items');
        
        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully.',
            'cart_items' => [],
            'cart_summary' => [
                'total_quantity' => 0,
                'total_amount' => 0
            ]
        ]);
    }

    // Get current cart items
    public function getCartItems()
    {
        try {
            $currentItems = session()->get('transaction_items', []);
            $cartItems = $this->getFormattedCartItems($currentItems);

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
            $product = Product::where('barcode', $request->barcode)->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found with this barcode.'
                ], 404);
            }

            $totalStock = Inventory::where('prod_code', $product->prod_code)->sum('stock');

            return response()->json([
                'success' => true,
                'product' => [
                    'prod_code' => $product->prod_code,
                    'name' => $product->name,
                    'cost_price' => $product->cost_price,
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

    // Process payment (reusing existing logic)
    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'amount_received' => 'required|numeric|min:0',
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
            $owner_id = null;
            $staff_id = null;
            
            if (Auth::guard('owner')->check()) {
                $owner_id = Auth::guard('owner')->user()->owner_id;
            } elseif (Auth::guard('staff')->check()) {
                $staff_id = Auth::guard('staff')->user()->staff_id;
                $owner_id = Auth::guard('staff')->user()->owner_id;
            }

            $receipt = Receipt::create([
                'receipt_date' => now(),
                'owner_id' => $owner_id ?? 1,
                'staff_id' => $staff_id
            ]);

            $totalAmount = 0;
            $lowStockProducts = [];

            foreach ($items as $item) {
                $product = Product::find($item['prod_code']);
                
                if (!$product) {
                    throw new \Exception("Product not found: " . $item['prod_code']);
                }

                $totalStock = Inventory::where('prod_code', $item['prod_code'])->sum('stock');
                
                if ($totalStock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: " . $product->name . ". Available: {$totalStock}");
                }

                ReceiptItem::create([
                    'item_quantity' => $item['quantity'],
                    'prod_code' => $item['prod_code'],
                    'receipt_id' => $receipt->receipt_id
                ]);

                $this->decrementInventoryStock($item['prod_code'], $item['quantity']);
                
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

    // Helper method to format cart items
    private function getFormattedCartItems($items)
    {
        $formattedItems = [];
        $totalAmount = 0;
        $totalQuantity = 0;

        foreach ($items as $item) {
            $product = Product::find($item['prod_code']);
            if ($product) {
                $currentStock = Inventory::where('prod_code', $item['prod_code'])->sum('stock');
                $itemTotal = $product->cost_price * $item['quantity'];
                
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

    private function decrementInventoryStock($prod_code, $quantity)
    {
        $remainingQuantity = $quantity;
        
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

    private function generateReceiptNumber()
    {
        $lastReceipt = Receipt::orderBy('receipt_id', 'desc')->first();
        return $lastReceipt ? $lastReceipt->receipt_id + 1 : 1;
    }
}