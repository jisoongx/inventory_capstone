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
use App\Http\Controllers\ActivityLogController;

class StoreController extends Controller
{
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
                ->leftJoin('inventory', 'receipt_item.inven_code', '=', 'inventory.inven_code')
                ->select(
                    'receipt_item.*', 
                    'products.name as product_name', 
                    'products.selling_price',
                    'products.vat_category',
                    'inventory.batch_number',
                    'inventory.expiration_date'
                )
                ->where('receipt_item.receipt_id', $receiptId)
                ->get();

           
            // ----------------------
            // Include eligible bundles in response
            // ----------------------
            $storeInfo = DB::table('owners')
                ->select('store_name', 'store_address', 'contact')
                ->where('owner_id', $ownerId)
                ->first();

            return response()->json([
                'success' => true,
                'receipt' => $receipt,
                'items' => $items,
                'store_info' => $storeInfo,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showKioskTransaction()
    {
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
        
        $store_info = DB::table('owners')
            ->select('store_name', 'store_address', 'contact', 'tin_number')
            ->where('owner_id', $owner_id)
            ->first();

        return view('store_start_transaction', [
            'receipt_no' => $this->generateReceiptNumber($owner_id),
            'user_firstname' => $user_firstname,
            'store_info' => $store_info,
            'expired' => false
        ]);
    }

    public function showPaymentProcessor()
    {
        $owner_id = $this->getCurrentOwnerId();
        
        if (!$owner_id) {
            abort(403, 'Unauthorized access');
        }
        
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
        
        $store_info = DB::table('owners')
            ->select('store_name', 'store_address', 'contact', 'tin_number')
            ->where('owner_id', $owner_id)
            ->first();
        
        $receipt_no = $this->generateReceiptNumber($owner_id);
        
        return view('store_payment_processor', [
            'receipt_no' => $receipt_no,
            'user_firstname' => $user_firstname,
            'store_info' => $store_info,
            'expired' => false
        ]);
    }

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

    public function getKioskProducts(Request $request)
    {
        try {
            $ownerId = $this->getCurrentOwnerId();
            
            if (! $ownerId) {
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
                        ->where('inventory.owner_id', '=', $ownerId)
                        ->where(function($q) {
                            $q->whereNull('inventory.is_expired')
                              ->orWhere('inventory.is_expired', '=', 0);
                        })
                        ->where(function($q) {
                            $q->whereNull('inventory.expiration_date')
                              ->orWhere('inventory.expiration_date', '>', DB::raw('CURDATE()'));
                        });
                })
                ->select(
                    'products.prod_code',
                    'products.name',
                    'products.selling_price',
                    'products.vat_category',
                    'products.stock_limit',
                    'products.barcode',
                    'products.prod_image',
                    'products.category_id',
                    'categories.category as category_name',
                    DB::raw('COALESCE(SUM(inventory.stock), 0) as stock'),
                    DB::raw('(SELECT MIN(expiration_date) FROM inventory WHERE inventory.prod_code = products.prod_code AND inventory.owner_id = ' .  $ownerId . ' AND (inventory.is_expired IS NULL OR inventory.is_expired = 0) AND inventory.stock > 0 AND (expiration_date IS NULL OR expiration_date > CURDATE())) as nearest_expiration')
                )
                ->where('products.owner_id', $ownerId)
                ->where('products.prod_status', 'active')
                ->groupBy(
                    'products.prod_code',
                    'products.name',
                    'products.selling_price',
                    'products.vat_category',
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

            foreach ($products as $product) {
                if ($product->prod_image) {
                    $product->prod_image = asset('storage/' . $product->prod_image);
                }
                
                $product->has_expired_only = false;
                if ($product->stock == 0) {
                    $expiredStock = DB::table('inventory')
                        ->where('prod_code', $product->prod_code)
                        ->where('owner_id', $ownerId)
                        ->where(function($q) {
                            $q->where('is_expired', 1)
                              ->orWhere('expiration_date', '<=', DB::raw('CURDATE()'));
                        })
                        ->sum('stock');
                    if ($expiredStock > 0) {
                        $product->has_expired_only = true;
                    }
                }
            }
    
            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            \Log::error('Error loading products: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading products: ' . $e->getMessage()
            ], 500);
        }
    }

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
            
            $product = Product::where('prod_code', $request->prod_code)
                            ->where('owner_id', $ownerId)
                            ->first();
            
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found or access denied.'
                ], 404);
            }
    
            $availableBatches = Inventory::where('prod_code', $product->prod_code)
                                    ->where('owner_id', $ownerId)
                                    ->where('stock', '>', 0)
                                    ->where(function($q) {
                                        $q->whereNull('is_expired')
                                            ->orWhere('is_expired', 0);
                                    })
                                    ->where(function($q) {
                                        $q->whereNull('expiration_date')
                                          ->orWhere('expiration_date', '>', DB::raw('CURDATE()'));
                                    })
                                    ->orderByRaw('
                                        CASE 
                                            WHEN expiration_date IS NOT NULL THEN 0
                                            ELSE 1
                                        END,
                                        expiration_date ASC,
                                        batch_number ASC
                                    ')
                                    ->get();
    
            if ($availableBatches->isEmpty()) {
                $expiredStock = Inventory::where('prod_code', $product->prod_code)
                                        ->where('owner_id', $ownerId)
                                        ->where(function($q) {
                                            $q->where('is_expired', 1)
                                              ->orWhere('expiration_date', '<=', DB::raw('CURDATE()'));
                                        })
                                        ->sum('stock');
                
                if ($expiredStock > 0) {
                    return response()->json([
                        'success' => false,
                        'expired_product' => true,
                        'message' => 'This product only has expired stock and cannot be sold.',
                        'product_name' => $product->name
                    ], 400);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'This product is out of stock.',
                    'product_name' => $product->name
                ], 400);
            }
    
            $totalStock = $availableBatches->sum('stock');
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
    
            $allocatedBatches = $this->allocateInventoryBatches($availableBatches, $newTotalQuantity);
    
            $itemFound = false;
            foreach ($currentItems as &$item) {
                if ($item['prod_code'] == $request->prod_code) {
                    $item['quantity'] = $newTotalQuantity;
                    $item['allocated_batches'] = $allocatedBatches;
                    $itemFound = true;
                    break;
                }
            }
    
            if (!$itemFound) {
                $currentItems[] = [
                    'prod_code' => $request->prod_code,
                    'quantity' => $request->quantity,
                    'allocated_batches' => $allocatedBatches
                ];
            }
    
            session()->put('transaction_items', $currentItems);
    
            $cartItems = $this->getFormattedCartItems($currentItems, $ownerId);
    
            return response()->json([
                'success' => true,
                'message' => $product->name . ' added to cart successfully! ',
                'cart_items' => $cartItems['items'],
                'cart_summary' => [
                    'total_quantity' => $cartItems['total_quantity'],
                    'total_amount' => $cartItems['total_amount']
                ],
                'newly_added_prod_code' => $request->prod_code
            ]);
    
        } catch (\Exception $e) {
            \Log::error('Error adding to cart: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error adding item: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function allocateInventoryBatches($availableBatches, $requiredQuantity)
    {
        $allocated = [];
        $remainingQuantity = $requiredQuantity;

        $sortedBatches = $availableBatches->sortBy('expiration_date');

        foreach ($sortedBatches as $batch) {
            if ($remainingQuantity <= 0) break;

            $takeFromBatch = min($batch->stock, $remainingQuantity);
            
            $allocated[] = [
                'inven_code' => $batch->inven_code,
                'batch_number' => $batch->batch_number,
                'expiration_date' => $batch->expiration_date,
                'quantity' => $takeFromBatch,
                'current_stock' => $batch->stock
            ];

            $remainingQuantity -= $takeFromBatch;
        }

        return $allocated;
    }

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
                $availableBatches = Inventory::where('prod_code', $request->prod_code)
                                        ->where('owner_id', $ownerId)
                                        ->where('stock', '>', 0)
                                        ->where(function($q) {
                                            $q->whereNull('is_expired')
                                                ->orWhere('is_expired', 0);
                                        })
                                        ->where(function($q) {
                                            $q->whereNull('expiration_date')
                                              ->orWhere('expiration_date', '>', DB::raw('CURDATE()'));
                                        })
                                        ->orderByRaw('
                                            CASE 
                                                WHEN expiration_date IS NOT NULL THEN 0
                                                ELSE 1
                                            END,
                                            expiration_date ASC,
                                            batch_number ASC
                                        ')
                                        ->get();
    
                $totalStock = $availableBatches->sum('stock');
                
                if ($totalStock < $request->quantity) {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stock. Available: {$totalStock}"
                    ], 400);
                }
    
                $allocatedBatches = $this->allocateInventoryBatches($availableBatches, $request->quantity);
    
                $itemFound = false;
                foreach ($currentItems as &$item) {
                    if ($item['prod_code'] == $request->prod_code) {
                        $item['quantity'] = $request->quantity;
                        $item['allocated_batches'] = $allocatedBatches;
                        $itemFound = true;
                        break;
                    }
                }
    
                if (!$itemFound) {
                    $currentItems[] = [
                        'prod_code' => $request->prod_code,
                        'quantity' => $request->quantity,
                        'allocated_batches' => $allocatedBatches
                    ];
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

    public function removeCartItem(Request $request)
    {
        $request->validate([
            'prod_code' => 'required|exists:products,prod_code'
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
            $found = false;
    
            $currentItems = array_filter($currentItems, function($item) use ($request, &$found) {
                if ($item['prod_code'] == $request->prod_code) {
                    $found = true;
                    return false;
                }
                return true;
            });
    
            if (!$found) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in cart'
                ], 404);
            }
    
            $currentItems = array_values($currentItems);
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
                'message' => 'Error removing item: ' .  $e->getMessage()
            ], 500);
        }
    }


    public function getCartItems()
    {
        try {
            $ownerId = $this->getCurrentOwnerId();
            $currentItems = session()->get('transaction_items', []);
            $formattedData = $this->getFormattedCartItems($currentItems, $ownerId);
            
            $cartItems = $formattedData['items'] ?? [];
            $totalQuantity = $formattedData['total_quantity'] ?? 0;
            $totalAmount = $formattedData['total_amount'] ?? 0;
            
            // Option 2: If it returns just the items array directly
            // $cartItems = $formattedData;
            // $totalQuantity = array_sum(array_column($cartItems, 'quantity'));
            // $totalAmount = array_sum(array_column($cartItems, 'total'));

            // ----------------------
            // Build cart quantities
            // ----------------------
            $cartQuantities = [];
            foreach ($cartItems as $item) {
                $prodCode = $item['prod_code'] ?? $item['product']['prod_code'] ?? null;
                if ($prodCode) {
                    $cartQuantities[$prodCode] = ($cartQuantities[$prodCode] ?? 0) + ($item['quantity'] ?? 0);
                }
            }

            // Only proceed with bundle logic if we have items
            $eligibleBundles = [];
            $eligibleBundlesByProduct = [];

            if (!empty($cartQuantities)) {
                // --------------------------------
                // Step 2: Fetch bundle rules with business priority
                // --------------------------------
                $bundleRules = DB::table('vw_active_bundle_items')
                    ->orderBy("priority_level")
                    ->get();

                // --------------------------------
                // Step 3: Build product → bundle map
                // --------------------------------
                $productBundleMap = [];
                foreach ($bundleRules as $rule) {
                    $productBundleMap[$rule->prod_code][] = $rule->bundle_id;
                }

                // --------------------------------
                // Step 4: Attach bundle IDs to cart items
                // --------------------------------
                foreach ($cartItems as &$cartItem) {
                    $prodCode = $cartItem['prod_code'] ?? $cartItem['product']['prod_code'] ?? null;
                    $cartItem['bundle_ids'] = $prodCode ? ($productBundleMap[$prodCode] ?? []) : [];
                }
                unset($cartItem);

                // --------------------------------
                // Step 5: Determine eligible bundles and subtract quantities
                // --------------------------------
                $bundlesGrouped = $bundleRules->groupBy('bundle_id');

                foreach ($bundlesGrouped as $bundleId => $bundleItems) {
                    $isEligible = true;

                    // Sum required quantities per product
                    $requiredPerProduct = [];
                    foreach ($bundleItems as $rule) {
                        $requiredPerProduct[$rule->prod_code] = 
                            ($requiredPerProduct[$rule->prod_code] ?? 0) + $rule->required_qty;
                    }

                    // Check if the cart contains enough for every product in the bundle
                    foreach ($requiredPerProduct as $prodCode => $totalRequired) {
                        $availableQty = $cartQuantities[$prodCode] ?? 0;
                        if ($availableQty < $totalRequired) {
                            $isEligible = false;
                            break;
                        }
                    }

                    if ($isEligible) {
                        $eligibleBundles[$bundleId] = $bundleItems;

                        // Subtract quantities used by this bundle
                        foreach ($requiredPerProduct as $prodCode => $totalRequired) {
                            $cartQuantities[$prodCode] -= $totalRequired;
                        }

                        foreach ($eligibleBundles as $bundleId => $bundleItems) {
                            foreach ($bundleItems as $rule) {
                                $prodCode = $rule->prod_code;
                                $eligibleBundlesByProduct[$prodCode][] = [
                                    'bundle_id' => $rule->bundle_id,
                                    'bundle_code' => $rule->bundle_code,
                                    'discount_percent' => $rule->discount_percent,
                                    'bogoType' => $rule->bogoType,
                                    'required_qty' => $rule->required_qty
                                ];
                            }
                        }
                    }
                }

            }

            // ✅ Return JSON with cart and eligible bundles
            return response()->json([
                'success' => true,
                'cart_items' => $cartItems,
                'cart_summary' => [
                    'total_quantity' => $totalQuantity,
                    'total_amount' => $totalAmount
                ],
                'eligibleBundles' => $eligibleBundles,
                'eligibleBundlesByProduct' => $eligibleBundlesByProduct,
                'requireConfirmation' => count($eligibleBundles) > 0
            ]);

        } catch (\Exception $e) {
            \Log::error('Get Cart Items Error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting cart items: ' . $e->getMessage()
            ], 500);
        }
    }


    public function processBarcodeSearch(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        try {
            $ownerId = $this->getCurrentOwnerId();
            
            if (! $ownerId) {
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
                                   ->where(function($q) {
                                       $q->whereNull('is_expired')
                                         ->orWhere('is_expired', 0);
                                   })
                                   ->sum('stock');

            return response()->json([
                'success' => true,
                'product' => [
                    'prod_code' => $product->prod_code,
                    'name' => $product->name,
                    'selling_price' => $product->selling_price,
                    'vat_category' => $product->vat_category,
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
                'message' => 'Error processing barcode: ' .  $e->getMessage()
            ], 500);
        }
    }
    

    public function processPayment(Request $request) 
    {
        $request->validate([
            'payment_method' => 'required|string',
            'amount_paid' => 'required|numeric|min:0',
            'receipt_discount_type' => 'nullable|in:percent,amount',
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
            
            if (!  $owner_id) {
                throw new \Exception('Unauthorized access');
            }
            
            if (Auth::guard('staff')->check()) {
                $staff_id = Auth::guard('staff')->user()->staff_id;
            }
    
            $receiptDiscountType = $request->input('receipt_discount_type', 'percent');
            if ($receiptDiscountType === 'fixed') {
                $receiptDiscountType = 'amount';
            }
            
            $receiptDiscountValue = $request->input('receipt_discount_value', 0);
            $vatEnabled = $request->input('vat_enabled', false);
            $vatRate = $request->input('vat_rate', 0);
            $itemDiscounts = $request->input('item_discounts', []);
    
            $hasItemDiscounts = false;
            foreach ($itemDiscounts as $discount) {
                if ($discount['value'] > 0) {
                    $hasItemDiscounts = true;
                    break;
                }
            }
    
            $hasReceiptDiscount = $receiptDiscountValue > 0;
    
            if ($hasItemDiscounts && $hasReceiptDiscount) {
                throw new \Exception('Cannot apply both item discounts and receipt discount at the same time.');
            }
    
            $lowStockProducts = [];
            $receiptItems = [];
            $subtotal = 0;
    
            foreach ($items as $item) {
                $product = Product::where('prod_code', $item['prod_code'])
                                ->where('owner_id', $owner_id)
                                ->first();
                
                if (! $product) {
                    throw new \Exception("Product not found or access denied: " . $item['prod_code']);
                }

                foreach ($item['allocated_batches'] as $batch) {
                    $currentBatchStock = Inventory::where('inven_code', $batch['inven_code'])
                                                ->where('owner_id', $owner_id)
                                                ->value('stock');
                    
                    if ($currentBatchStock < $batch['quantity']) {
                        throw new \Exception("Insufficient stock in batch {$batch['batch_number']} for product: " . $product->name .  ". Available: {$currentBatchStock}, Requested: {$batch['quantity']}");
                    }
                }
    
                $itemTotal = $product->selling_price * $item['quantity'];
                $subtotal += $itemTotal;
    
                $receiptItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'amount' => $itemTotal,
                    'allocated_batches' => $item['allocated_batches']
                ];

            }

    
            
            $totalItemDiscounts = 0;
            $itemDiscountAmounts = [];

            foreach ($items as $item) {
                $product = Product::where('prod_code', $item['prod_code'])->first();
                $pricePerUnit = $product->selling_price;
                
                $calculatedDiscountAmountPerUnit = 0;
                
                if (isset($itemDiscounts[$item['prod_code']])) {
                    $discount = $itemDiscounts[$item['prod_code']];
                    $discountType = $discount['type'] === 'fixed' ? 'amount' : $discount['type'];
                    
                    if ($discountType === 'percent') {
                        
                        $calculatedDiscountAmountPerUnit = $pricePerUnit * ($discount['value'] / 100);
                    } else {
                        
                        $calculatedDiscountAmountPerUnit = min($discount['value'], $pricePerUnit); 
                    }
                }
                
            
                $totalDiscountForItem = $calculatedDiscountAmountPerUnit * $item['quantity'];
                $itemDiscountAmounts[$item['prod_code']] = $totalDiscountForItem;
                $totalItemDiscounts += $totalDiscountForItem;
            }

            $afterItemDiscounts = $subtotal - $totalItemDiscounts;

            $cartItemsResponse = $this->getCartItems();
            $eligibleBundles = $cartItemsResponse->original['eligibleBundles'] ?? [];
            $eligibleBundlesByProduct = $cartItemsResponse->original['eligibleBundlesByProduct'] ?? [];

            $afterDiscount = 0;
            $bundleAppliedUnits = 0;

            // -----------------------PROMO DISCOUNT----------------
            foreach ($items as $item) {
                $product = Product::where('prod_code', $item['prod_code'])->first();
                $pricePerUnit = $product->selling_price;

                $totalItemDiscount = 0;
                $totalPromoDiscount = 0;
                $bundleAppliedUnits = 0;

                
                if (isset($itemDiscounts[$item['prod_code']])) {
                    $discount = $itemDiscounts[$item['prod_code']];
                    $type = $discount['type'] === 'fixed' ? 'amount' : $discount['type'];

                    $discountPerUnit = $type === 'percent'
                        ? $pricePerUnit * ($discount['value'] / 100)
                        : min($discount['value'], $pricePerUnit);

                    $totalItemDiscount = $discountPerUnit * $item['quantity'];
                }

                
                if (isset($eligibleBundles[$item['prod_code']])) {
                    foreach ($eligibleBundles[$item['prod_code']] as $bundle) {
                        $bundleId = $bundle['bundle_id'];
                        $bundleCode = $bundle['bundle_code'];
                        $ruleQty = $bundle['required_qty'] ?? 1;

                        $remainingQty = $item['quantity'] - $bundleAppliedUnits;
                        if ($remainingQty <= 0) break;

                        $applyQty = min($ruleQty, $remainingQty);
                        $bundlePercent = $bundleSettings[$bundleId]->discount_percent ?? 0;

                        
                        $discountPerUnit = $pricePerUnit * ($bundlePercent / 100);
                        $discountAmountForBundle = $discountPerUnit * $applyQty;

                        
                        $bundleAppliedUnits += $applyQty;

                        
                        if (!isset($itemBundleDiscounts[$bundleCode])) {
                            $itemBundleDiscounts[$bundleCode] = [
                                'bundle_percent' => $bundlePercent,
                                'total_discount' => 0,
                                'apply_qty' => 0
                            ];
                        }
                        
                        $itemBundleDiscounts[$bundleCode]['total_discount'] += $discountAmountForBundle;
                        $itemBundleDiscounts[$bundleCode]['apply_qty'] += $applyQty;
                        
                        
                        $totalPromoDiscount += $discountAmountForBundle;
                    }
                }

                $totalDiscount = $totalItemDiscount + $totalPromoDiscount;
                $afterDiscount += $pricePerUnit * $item['quantity'] - $totalDiscount;
            }

            $afterPromoDiscounts = $afterItemDiscounts - $afterDiscount;


            
            $receiptDiscountAmount = 0;

            if (! $hasItemDiscounts && $hasReceiptDiscount) {
                if ($receiptDiscountType === 'percent') {
                    $receiptDiscountAmount = $afterPromoDiscounts * ($receiptDiscountValue / 100);
                } else {
                    // FIXED receipt discount — apply ONCE
                    $receiptDiscountAmount = min($receiptDiscountValue, $afterPromoDiscounts);
                }
            }

            $afterReceiptDiscount = $afterPromoDiscounts - $receiptDiscountAmount;


            // new vat changes
            $vatAmountInclusive = 0;
            $vatAmountExempt = 0;

            if ($vatEnabled && $vatRate > 0) {
                foreach ($items as $item) {
                    $product = Product::where('prod_code', $item['prod_code'])->first();
                    $pricePerUnit = $product->selling_price;
                    $totalQty = $item['quantity'];
                    
                    // Calculate item discount
                    $itemDiscountAmount = 0;
                    if (isset($itemDiscounts[$item['prod_code']])) {
                        $discount = $itemDiscounts[$item['prod_code']];
                        $discountType = $discount['type'] === 'fixed' ? 'amount' : $discount['type'];
                        
                        if ($discountType === 'percent') {
                            $itemDiscountAmount = ($pricePerUnit * ($discount['value'] / 100)) * $totalQty;
                        } else {
                            $itemDiscountAmount = min($discount['value'], $pricePerUnit) * $totalQty;
                        }
                    }
                    
                    // Calculate bundle discount
                    $bundleDiscountAmount = 0;
                    if (isset($eligibleBundlesByProduct[$item['prod_code']])) {
                        foreach ($eligibleBundlesByProduct[$item['prod_code']] as $bundle) {
                            $bundlePercent = (float) ($bundle['discount_percent'] ??  0);
                            $requiredQty = (int) ($bundle['required_qty'] ?? 1);
                            $bogoType = $bundle['bogoType'] ?? null;
                            
                            $applyQty = min($requiredQty, $totalQty);
                            
                            if ($bogoType === null && $bundlePercent > 0) {
                                $bundleDiscountAmount += ($pricePerUnit * ($bundlePercent / 100)) * $applyQty;
                            }
                        }
                    }
                    
                    // Calculate item total after item and bundle discounts
                    $itemGross = $pricePerUnit * $totalQty;
                    $itemAfterItemDiscount = $itemGross - $itemDiscountAmount;
                    $itemAfterBundleDiscount = $itemAfterItemDiscount - $bundleDiscountAmount;
                    
                    // Apply proportional receipt discount
                    $itemShare = $subtotal > 0 ? ($itemGross / $subtotal) : 0;
                    $itemReceiptDiscount = $receiptDiscountAmount * $itemShare;
                    $itemFinalAmount = $itemAfterBundleDiscount - $itemReceiptDiscount;
                    
                    // Calculate VAT based on final amount
                    if ($product->vat_category === 'vat_inclusive') {
                        $vatAmountInclusive += $itemFinalAmount * ($vatRate / (100 + $vatRate));
                    } else {
                        $vatAmountExempt += $itemFinalAmount;
                    }
                }
            }

            $totalVatAmount = $vatAmountInclusive;
    
            $totalAmount = $afterReceiptDiscount;
    
            $paidRounded  = round($request->amount_paid, 2);
            $totalRounded = $totalAmount;

            if ($paidRounded < $totalRounded) {
                throw new \Exception(
                    "Amount paid (₱" . number_format($paidRounded, 2) . 
                    ") is less than total amount (₱" . number_format($totalRounded, 2) . ")" . 
                    "\n" . number_format($afterDiscount, 2) . "   - " . number_format($afterReceiptDiscount, 2) 
                );
            }
    
            $receipt = Receipt::create([
                'receipt_date' => now(),
                'owner_id' => $owner_id,
                'staff_id' => $staff_id,
                'amount_paid' => $request->amount_paid,
                'discount_type' => $receiptDiscountType,
                'discount_value' => $receiptDiscountValue,
                'discount_amount' => $receiptDiscountValue 
            ]);
    
            foreach ($items as $item) {
                $product = Product::where('prod_code', $item['prod_code'])->first();
                $pricePerUnit = $product->selling_price;
                $totalQty = $item['quantity'];

                $allocatedReceiptItems = [];
                $remainingQty = $totalQty;

                if (isset($eligibleBundlesByProduct[$item['prod_code']])) {
                    foreach ($eligibleBundlesByProduct[$item['prod_code']] as $bundle) {
                        if ($remainingQty <= 0) break;

                        $bundleCode = $bundle['bundle_code'];
                        $discountPercent = (float) ($bundle['discount_percent'] ?? 0);
                        $requiredQty = (int) ($bundle['required_qty'] ?? 1);
                        $bogoType = $bundle['bogoType'] ?? null;

                        $applyQty = min($requiredQty, $remainingQty);
                        if ($applyQty <= 0) continue;

                        if ($bogoType === null) {
                            $allocatedReceiptItems[] = [
                                'quantity' => $applyQty,
                                'item_discount_type' => 'bundle',
                                'item_receipt_type' => $bundleCode,
                                'item_discount_value' => $discountPercent,
                                'item_discount_amount' => $pricePerUnit * ($discountPercent / 100),
                            ];
                        } else {
                            $allocatedReceiptItems[] = [
                                'quantity' => $applyQty,
                                'item_discount_type' => 'normal',
                                'item_receipt_type' => 'regular',
                                'item_discount_value' => 0,
                                'item_discount_amount' => 0,
                            ];
                        }

                        $remainingQty -= $applyQty;
                    }
                }

                if ($remainingQty > 0) {
                    $allocatedReceiptItems[] = [
                        'quantity' => $remainingQty,
                        'item_discount_type' => 'normal',
                        'item_receipt_type' => 'regular',
                        'item_discount_value' => 0,
                        'item_discount_amount' => 0,
                    ];
                }

                foreach ($item['allocated_batches'] as $batch) {
                    $batchProportion = $batch['quantity'] / $totalQty;

                    foreach ($allocatedReceiptItems as $alloc) {
                        $qtyForBatch = (int) round($alloc['quantity'] * $batchProportion);
                        if ($qtyForBatch <= 0) continue;

                        ReceiptItem::create([
                            'prod_code' => $item['prod_code'],
                            'receipt_id' => $receipt->receipt_id,
                            'item_quantity' => $qtyForBatch,
                            'item_discount_type' => $alloc['item_discount_type'],
                            'item_receipt_type' => $alloc['item_receipt_type'],
                            'item_discount_value' => $alloc['item_discount_value'],
                            'item_discount_amount' => $alloc['item_discount_amount'],
                            'vat_amount' => 0,
                            'inven_code' => $batch['inven_code'],
                        ]);

                        Inventory::where('inven_code', $batch['inven_code'])
                            ->where('owner_id', $owner_id)
                            ->decrement('stock', $qtyForBatch);
                    }
                }
            }


    
            session()->forget('transaction_items');
    
            DB::commit();
    
            // Activity log
            $user = Auth::guard('owner')->user() ??  Auth::guard('staff')->user();
            $ip = $request->ip();
            
            ActivityLogController::log(
                'Processed POS transaction',
                $user instanceof \App\Models\Owner ? 'owner' : 'staff',
                $user,
                $ip
            );
    
            $response = [
                'success' => true,
                'message' => 'Transaction completed successfully! ',
                'receipt_id' => $receipt->receipt_id,
                'subtotal' => $subtotal,
                'total_item_discounts' => $totalItemDiscounts,
                'total_promo_discounts' => $afterDiscount,
                'receipt_discount_amount' => $receiptDiscountAmount,
                'vat_amount' => $totalVatAmount,
                'vat_amount_inclusive' => $vatAmountInclusive,
                'vat_amount_exempt' => $vatAmountExempt,
                'total_amount' => $totalAmount,
                'amount_paid' => $request->amount_paid,
                'change' => $request->amount_paid - $totalAmount,
                'receipt_items' => $receiptItems,
                'total_quantity' => array_sum(array_column($items, 'quantity')),
            ];
    
            if (! empty($lowStockProducts)) {
                $response['low_stock_warning'] = $lowStockProducts;
            }
    
            return response()->json($response);
    
        } catch (\Exception $e) {
            DB::rollBack();
            
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

    private function getFormattedCartItems($items, $ownerId)
    {
        try {
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
                                            ->where(function($q) {
                                                $q->whereNull('is_expired')
                                                ->orWhere('is_expired', 0);
                                            })
                                            ->sum('stock');
                    
                    $itemTotal = $product->selling_price * $item['quantity'];
                    
                    $formattedItems[] = [
                        'prod_code' => $product->prod_code,
                        'inven_code' => $item['allocated_batches'][0]['inven_code'] ?? null,
                        'name' => $product->name,
                        'selling_price' => $product->selling_price,
                        'quantity' => $item['quantity'],
                        'subtotal' => $itemTotal,
                        'current_stock' => $currentStock,
                        'allocated_batches' => $item['allocated_batches'] ?? [],
                        'product' => $product,
                        'amount' => $itemTotal
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
        } catch (\Exception $e) {
            \Log::error('Error in getFormattedCartItems', [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            throw $e;
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
        
        $date = $request->get('date');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        
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
        
        $years = DB::table('receipt')
            ->select(DB::raw('DISTINCT YEAR(receipt_date) as year'))
            ->where('owner_id', $ownerId)
            ->orderBy('year', 'desc')
            ->get();
        
        $monthNames = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];
        
        $sbc = collect();
        
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