<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreateBundle extends Component
{
    public $selectedProducts = [];
    public $bundleName = '';
    public $bundleCode = '';
    public $bundlePrice = '';
    public $bundleQty = 1;
    public $bundleCategory;
    public $bundleType;
    public $status;
    public $startDate;
    public $endDate;
    public $bogoType;
    public $bundleObjective;
    public $bundlePriority;
    public $bundleGoal;

    public $test;

    public $products;
    public $batchDetails;

    public $activeTab = 'generate';
    public $expandedProductCode = null;

    public $qtyInputType = 'manual';
    public $discount = 'manual';
    public $step = 1;

    public $minMargin; 
    public $pricingPreview = [];
    public $minProfit;

    public $selectedDiscount = null;
    public $newBundlePrice;
    public $discountType = 'predefined';
    public $discountValue = null;
    public $freeBundlePrice;
    public $regularBundlePrice;

    public $searchWord = '';

    public array $suggestedBundles = [];
    public array $allBundle = [];
    public $showBundles = false;


    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }


    public function toggleProduct($prodCode)
    {
        $prod = $this->products->firstWhere('prod_code', $prodCode);
        if (!$prod) return;

        // Check if any row of this product is already selected
        $isSelected = collect($this->selectedProducts)->contains(function($data) use ($prodCode) {
            return $data['prod_code'] === $prodCode;
        });

        if ($isSelected) {
            // Remove all rows for this product
            foreach ($this->selectedProducts as $key => $data) {
                if ($data['prod_code'] === $prodCode) {
                    unset($this->selectedProducts[$key]);
                }
            }
            return;
        }

        // Count distinct products currently selected
        $currentProducts = collect($this->selectedProducts)
            ->pluck('prod_code')
            ->unique()
            ->count();
        
        $futureProductsCount = $currentProducts + 1;
        $isSingleProductBogo = in_array($this->bundleType, ['BOGO1','BOGO2']) && $currentProducts === 0;

        if ($isSingleProductBogo) {
            // Add two entries for the same product with different quantities
            $this->selectedProducts[$prodCode.'_1'] = [
                'prod_code' => $prodCode,
                'selected' => true,
                'product_name' => $prod->name,
                'product_barcode' => $prod->barcode,
                'quantity' => 1,
                'bogo_type' => null, // will be set via setBogoType
            ];

            $this->selectedProducts[$prodCode.'_2'] = [
                'prod_code' => $prodCode,
                'selected' => true,
                'product_name' => $prod->name,
                'product_barcode' => $prod->barcode,
                'quantity' => 2,
                'bogo_type' => null, // will be set via setBogoType
            ];

            return;
        }

        // Normal product toggle for multi-product bundles
        $this->selectedProducts[$prodCode] = [
            'prod_code' => $prodCode,
            'selected' => true,
            'product_name' => $prod->name,
            'product_barcode' => $prod->barcode,
            'quantity' => $this->bundleQty,
            'bogo_type' => null,
        ];

        if ($futureProductsCount > 1) {
            foreach ($this->selectedProducts as $key => $data) {
                if (str_ends_with($key, '_2')) {
                    unset($this->selectedProducts[$key]);
                }
            }
        }
    }


    public function removeProduct($prodCodeKey)
    {
        unset($this->selectedProducts[$prodCodeKey]);
    }










    public function toggleBatch($prodCode)
    {
        DB::connection()->getPdo()->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");

        // collapse if same product clicked
        if ($this->expandedProductCode === $prodCode) {
            $this->expandedProductCode = null;
            return;
        }

        $this->expandedProductCode = $prodCode;

        // load only if not loaded yet
        if (!isset($this->batchDetails[$prodCode])) {
            $this->batchDetails[$prodCode] = DB::select("
                SELECT batch_number, stock, expiration_date
                FROM inventory
                WHERE prod_code = ?
                ORDER BY inven_code ASC
            ", [$prodCode]);
        }
    }


    protected $rules = [
        'bundleName'  => 'required|string|max:255',
        'bundleObjective'  => 'required|string|max:255',
        'bundleCode'  => 'required|string|max:100|unique:bundles,bundle_code',
        'bundlePrice' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'bundleObjective.required'  => 'Bundle objective is required.',
        'bundlePriority.required'  => 'Bundle priority is required.',
        'bundleName.required'  => 'Bundle name is required.',
        'bundleCode.required'  => 'Bundle code is required.',
        'bundleCode.unique'    => 'This bundle code already exists.',
        'bundlePrice.required' => 'Bundle price is required.',
        'bundlePrice.numeric'  => 'Bundle price must be a valid number.',
    ];

    

    public function updatedDiscountType($value)
    {
        if ($value === 'predefined') {
            $this->discountValue = $this->selectedDiscount;
        } else {
            $this->discountValue = null; 
        }
    }

    public $discountError = null;

    public function createBundle()
    {
        $owner_id = Auth::guard('owner')->user()->owner_id;

        if (!Auth::guard('owner')->check()) {
            abort(403, 'Unauthorized access.');
        }

        // Only selected products
        $selectedItems = collect($this->selectedProducts)
            ->filter(fn($item) => isset($item['selected']) && $item['selected']);

        if ($selectedItems->isEmpty()) {
            session()->flash('error', 'Please select at least one product.');
            return;
        }

        if($this->status == 'ACTIVE') {
            if(empty($this->startDate) || empty($this->endDate)) {
                $this->startDate = Carbon::now()->toDateString();
                return;
            }
        }

        if($this->bundleType != 'BOGO2' && $this->selectedDiscount === null) {  
            $this->discountError = "Cannot proceed. Choose a discount to proceed.";
            return;
        } else {
            $this->discountError = null;
        }

        $this->validate([
            'bundleCode' => 'required|unique:bundles,bundle_code',
            'bundleName' => 'required',
            'bundleObjective' => 'required',
            'bundlePriority' => 'required',
            'bundleCategory' => 'required',
            'bundleType' => 'required',
            'status' => 'required',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date|after_or_equal:startDate',
            'minProfit' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Insert bundle
            $bundleId = DB::table('bundles')->insertGetId([
                'bundle_code' => $this->bundleCode,
                'bundle_name' => $this->bundleName,
                'bundle_objective' => $this->bundleObjective,
                'bundle_notes' => $this->bundleGoal,
                'priority_level' => $this->bundlePriority,
                'bundle_category' => $this->bundleCategory,
                'bundle_type' => $this->bundleType,
                'status' => $this->status,
                'owner_id' => $owner_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            // Insert bundle settings
            DB::table('bundles_setting')->insert([
                'bundle_id' => $bundleId,
                'start_date'=> $this->startDate ?? now(),
                'end_date'=> $this->endDate ?? now(),
                'min_margin' => $this->minProfit ?? 0,
                'discount_percent' => $this->selectedDiscount ?? 0,
            ]);

            // Insert bundle items
            $rows = [];
            foreach ($selectedItems as $item) {
                $rows[] = [
                    'bundle_id' => $bundleId,
                    'prod_code' => $item['prod_code'] ?? null,
                    'quantity'  => $item['quantity'] ?? 1,
                    'bogoType'  => $item['bogo_type'] ?? null,
                ];
            }
            DB::table('bundle_items')->insert($rows);

            DB::commit();

            session()->flash('success', 'Bundle created successfully!');
            $this->resetForm();

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }


    public function resetForm()
    {
        $this->reset([
            'selectedProducts',
            'bundleName',
            'bundleCode',
            'startDate',
            'endDate',
            'minProfit',
            'selectedDiscount',
            'bundleType',
            'status',
        ]);

        $this->resetValidation();
    }

    public $nextStep;
    
    public function updatedNextStep()
    {
        $this->calculatePricingPreview();
    }

    public function updatedSelectedProducts($value, $key)
    {
        $this->calculatePricingPreview();
    }

    public function updatedMinProfit()
    {
        $this->calculatePricingPreview();
    }

    public function calculatePricingPreview()
    {
        $this->selectedDiscount = null;

        if (empty($this->selectedProducts)) {
            $this->pricingPreview = [];
            return;
        }

        $totalCost    = 0;
        $regularTotal = 0;
        $productDetails = [];

        foreach ($this->selectedProducts as $prodCode => $data) {
            if (!($data['selected'] ?? false)) {
                continue;
            }

            $qty = $data['quantity'] ?? 1;

            $product = DB::table('products')
                ->select('cost_price', 'selling_price')
                ->where('prod_code', $prodCode)
                ->first();

            if (!$product) {
                continue;
            }

            $totalCost    += $product->cost_price * $qty;
            $regularTotal += $product->selling_price * $qty;

            $productDetails[$prodCode] = [
                'product' => $product,
                'quantity' => $qty,
                'bogo_type' => $data['bogo_type'] ?? null,
                'cost_price' => $product->cost_price,
                'selling_price' => $product->selling_price,
            ];
        }

        if ($totalCost <= 0 || $regularTotal <= 0) {
            $this->pricingPreview = [];
            return;
        }

        $bundlePrice = $regularTotal;
        $profit      = $regularTotal - $totalCost;

        if ($this->bundleType === 'BOGO1') {
            // Buy X, Get Y Discounted
            $bundlePrice = 0;
            $totalCostBogo = 0;
            $totalQuantity = 0;
            $discountPercent = $this->selectedDiscount ?? 0;
            if (is_string($discountPercent)) {
                $discountPercent = (float) $discountPercent;
            }
            
            $xProducts = [];
            $yProducts = [];
            
            foreach ($productDetails as $prodCode => $data) {
                $p = $data['product'];
                $qty = $data['quantity'];
                $bogoType = $data['bogo_type'] ?? null;
                
                if ($bogoType == null) {
                    $yProducts[] = [
                        'prodCode' => $prodCode,
                        'selling_price' => $p->selling_price,
                        'cost_price' => $p->cost_price,
                        'quantity' => $qty,
                        'name' => $prodCode,
                    ];
                } else {
                    $xProducts[] = [
                        'prodCode' => $prodCode,
                        'selling_price' => $p->selling_price,
                        'cost_price' => $p->cost_price,
                        'quantity' => $qty,
                        'name' => $prodCode,
                    ];
                }
            }
            
            foreach ($xProducts as $product) {
                $bundlePrice += $product['selling_price'] * $product['quantity'];
                $totalCostBogo += $product['cost_price'] * $product['quantity'];
                $totalQuantity += $product['quantity'];
            }
            
            foreach ($yProducts as $product) {
                $discountMultiplier = 1 - ($discountPercent / 100);
                $bundlePrice += $product['selling_price'] * $product['quantity'] * $discountMultiplier;
                $totalCostBogo += $product['cost_price'] * $product['quantity'];
                $totalQuantity += $product['quantity'];
            }

            
            $profit = $bundlePrice - $totalCostBogo;
            
            $minProfit = max(0, (float) $this->minProfit);
            
            // If profit is less than minimum, adjust bundle price
            // if ($profit < $minProfit) {
            //     $bundlePrice = $totalCostBogo + $minProfit;
            //     $profit = $minProfit;
            // }
            
            $minBundlePrice = $totalCostBogo + $minProfit;
            if ($minBundlePrice > $regularTotal) {
                $minBundlePrice = $regularTotal;
            }
            
            $maxDiscountPercent = max(0, (($regularTotal - $minBundlePrice) / $regularTotal) * 100);
            
            $discountTiers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 25, 30,35, 40, 45, 50, 55, 60, 65, 70, 75];

            $discountOptions = collect($discountTiers)
                ->map(function ($yDiscountPercent) use ($regularTotal, $totalCostBogo, $minProfit, $yProducts, $xProducts) {
                    
                    $tierBundlePrice = 0;
                    
                    foreach ($xProducts as $product) {
                        $tierBundlePrice += $product['selling_price'] * $product['quantity'];
                    }
                    
                    foreach ($yProducts as $product) {
                        $discountMultiplier = 1 - ($yDiscountPercent / 100);
                        $tierBundlePrice += $product['selling_price'] * $product['quantity'] * $discountMultiplier;
                    }
                    
                    $tierProfit = round($tierBundlePrice - $totalCostBogo, 2);
                    
                    $effectiveDiscountPercent = round((($regularTotal - $tierBundlePrice) / $regularTotal) * 100, 2);
                    
                    return [
                        'y_discount_percent' => $yDiscountPercent, 
                        'discount_percent' => $effectiveDiscountPercent,
                        'bundle_price'     => round($tierBundlePrice, 2),
                        'profit'           => $tierProfit,
                        'calculation_note' => "{$yDiscountPercent}% on Y = {$effectiveDiscountPercent}% overall",
                    ];
                })
            ->filter(fn ($opt) => $opt['profit'] >= $minProfit)
            ->unique(fn ($opt) => $opt['bundle_price'].'-'.$opt['profit']) 
            ->values();

            
            $selectedBundlePrice = optional($discountOptions->first())['bundle_price'] ?? $bundlePrice;
            $matchingOption = $discountOptions->firstWhere('y_discount_percent', $discountPercent);
            
            $this->pricingPreview = [
                'total_cost'       => round($totalCostBogo, 2),
                'regular_price'    => round($regularTotal, 2),
                'bundle_price' => round($matchingOption['bundle_price'] ?? $bundlePrice, 2),
                'selected_bundle_price' => round($selectedBundlePrice, 2),
                'discount_offer'   => $this->selectedDiscount,
                'max_discount'     => round($maxDiscountPercent, 2),
                'expected_profit'  => round($profit, 2),
                'discount_options' => $discountOptions,
                'x_products'       => $xProducts,
                'y_products'       => $yProducts,
            ];
            
            return;

        } elseif ($this->bundleType === 'BOGO2') {
        
            $bundlePrice = 0;
            $totalCostBogo = 0;
            $totalQuantity = 0;
            $discountPercent = $this->selectedDiscount ?? 0;
            if (is_string($discountPercent)) {
                $discountPercent = (float) $discountPercent;
            }
            
            $xProducts = [];
            $yProducts = [];
            
            foreach ($productDetails as $prodCode => $data) {
                $p = $data['product'];
                $qty = $data['quantity'];
                $bogoType = $data['bogo_type'] ?? null;
                
                if ($bogoType == null) {
                    $yProducts[] = [
                        'prodCode' => $prodCode,
                        'selling_price' => $p->selling_price,
                        'cost_price' => $p->cost_price,
                        'quantity' => $qty,
                        'name' => $prodCode,
                    ];
                } else {
                    $xProducts[] = [
                        'prodCode' => $prodCode,
                        'selling_price' => $p->selling_price,
                        'cost_price' => $p->cost_price,
                        'quantity' => $qty,
                        'name' => $prodCode,
                    ];
                }
            }

            $this->regularBundlePrice = $qty * $data['selling_price'];
            
            foreach ($xProducts as $product) {
                $this->freeBundlePrice = $bundlePrice += $product['selling_price'] * $product['quantity'];
                $totalCostBogo += $product['cost_price'] * $product['quantity'];
                $totalQuantity += $product['quantity'];
            }

            
            $profit = $bundlePrice - $totalCostBogo;
            
            $minProfit = max(0, (float) $this->minProfit);
            
            $this->pricingPreview = [
                'total_cost'       => round($totalCostBogo, 2),
                'regular_price'    => round($regularTotal, 2),
                'bundle_price'     => round($bundlePrice, 2),
                'expected_profit'  => round($profit, 2),
                'discount_offer'   => $this->selectedDiscount,
                'discount_options' => collect([]),
            ];
            
            return;
        } else {
            
            $minProfit = max(0, (float) $this->minProfit);
            $minBundlePrice = $totalCost + $minProfit;
            
            if ($minBundlePrice > $regularTotal) {
                $minBundlePrice = $regularTotal;
            }
            
            $maxDiscountPercent = max(0, (($regularTotal - $minBundlePrice) / $regularTotal) * 100);
            
            $discountTiers = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 25, 30,35, 40, 45, 50, 55, 60, 65, 70, 75];
            
            $discountOptions = collect($discountTiers)
                ->map(function ($discount) use ($regularTotal, $totalCost, $minProfit) {
                    $bundlePrice = round($regularTotal * (1 - ($discount / 100)));
                    $profit = round($bundlePrice - $totalCost, 2);
                    $effectiveDiscountPercent = round((($regularTotal - $bundlePrice) / $regularTotal) * 100, 2);
                    
                    return [
                        'discount_percent' => $effectiveDiscountPercent,
                        'bundle_price'     => $bundlePrice,
                        'profit'           => $profit,
                    ];
                })
                ->filter(fn ($opt) => $opt['profit'] >= $minProfit)
                ->unique(fn ($opt) => $opt['bundle_price'].'-'.$opt['profit'])
                ->values();
            
            $selectedBundlePrice = optional($discountOptions->first())['bundle_price'] ?? null;
            
            $this->pricingPreview = [
                'total_cost'        => round($totalCost, 2),
                'regular_price'     => round($regularTotal, 2),
                'bundle_price'      => $selectedBundlePrice,
                'discount_offer'    => $this->selectedDiscount,
                'max_discount'      => round($maxDiscountPercent, 2),
                'expected_profit'   => round($minProfit, 2),
                'discount_options'  => $discountOptions,
            ];
        }
    }

    public function selectDiscount($discountPercent, $newBundlePrice)
    {
        $this->selectedDiscount = $discountPercent;
        $this->newBundlePrice = $newBundlePrice;

        $option = collect($this->pricingPreview['discount_options'] ?? [])
            ->firstWhere('discount_percent', $discountPercent);

        if (!$option) {
            return;
        }

    }

    public function setBogoType($rowKey)
    {
        if (!isset($this->selectedProducts[$rowKey])) {
            return;
        }

        // Toggle between:
        // - null = Regular product (BUY/PAY)
        // - 'P' = Promotional product (GET/FREE)
        
        $current = $this->selectedProducts[$rowKey]['bogo_type'] ?? null;
        
        if ($current === 'P') {
            // Changing from Promotional (GET) to Regular (BUY)
            $this->selectedProducts[$rowKey]['bogo_type'] = null;
        } else {
            // Changing from Regular (BUY) to Promotional (GET)
            $this->selectedProducts[$rowKey]['bogo_type'] = 'P';
        }

        $this->calculatePricingPreview();
    }



    public function updatedBundleType($value)
    {
        if (!in_array($value, ['BOGO1', 'BOGO2'])) {
            foreach ($this->selectedProducts as $code => $item) {
                $this->selectedProducts[$code]['bogo_type'] = null;
            }
            return;
        }
        foreach ($this->selectedProducts as $code => $item) {
            $this->selectedProducts[$code]['bogo_type'] = null;
        }
    }








    public function updatedSearchWord()
    {
        $this->render();
    }

    public function render()
    {
        $owner_id = Auth::guard('owner')->user()->owner_id;


        if (!Auth::guard('owner')->check()) {
            abort(403, 'Unauthorized access.');
        }

        DB::connection()->getPdo()->exec("SET SESSION sql_mode = REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', '')");

        if ($this->searchWord) {
            $this->products = collect(DB::select("
                SELECT
                    p.prod_code,
                    p.barcode,
                    p.name,
                    COALESCE(SUM(i.stock), 0) AS total_stock,
                    c.category,
                    COUNT(DISTINCT i.batch_number) AS batch_count
                FROM products p
                LEFT JOIN categories c
                    ON p.category_id = c.category_id
                LEFT JOIN inventory i
                    ON p.prod_code = i.prod_code
                WHERE p.name LIKE ?
                GROUP BY p.prod_code, p.name, c.category
                ORDER BY p.name
            ", ['%' . $this->searchWord . '%']));
        } else {
            $this->products = collect(DB::select("
                SELECT
                    p.prod_code,
                    p.barcode,
                    p.name,
                    COALESCE(SUM(i.stock), 0) AS total_stock,
                    c.category,
                    COUNT(DISTINCT i.batch_number) AS batch_count
                FROM products p
                LEFT JOIN categories c
                    ON p.category_id = c.category_id
                LEFT JOIN inventory i
                    ON p.prod_code = i.prod_code
                GROUP BY p.prod_code, p.name, c.category
                ORDER BY p.name
            "));
        }

        $this->loadAllBundle();
        

        return view('livewire.create-bundle');
    }




    public function loadAllBundle() {

        $owner_id = Auth::guard('owner')->user()->owner_id;

        if (!Auth::guard('owner')->check()) {
            abort(403, 'Unauthorized access.');
        }

        $this->allBundle = DB::select("
            SELECT b.bundle_id,
                b.bundle_code,
                b.bundle_name,
                b.bundle_category,
                b.bundle_type,
                b.status,
                b.owner_id,
                s.discount_percent,
                s.min_margin,
                s.start_date,
                s.end_date
            FROM bundles b
            LEFT JOIN bundles_setting s
                ON b.bundle_id = s.bundle_id
            where owner_id = ?
        ", [$owner_id]);

        
    }

    public $selectedBundle;

    public function selectBundle($bundleId)
    {
        $this->showBundles = true;
        
        $this->selectedBundle = DB::select("
            SELECT b.bundle_id,
                b.bundle_code,
                b.bundle_name,
                b.bundle_category,
                b.bundle_type,
                b.status,
                b.owner_id,
                s.discount_percent,
                s.min_margin,
                s.start_date,
                s.end_date,
                p.name, 
                bi.quantity
            FROM bundles b
            JOIN bundles_setting s
                ON b.bundle_id = s.bundle_id
            JOIN bundle_items bi 
                ON b.bundle_id = bi.bundle_id
            JOIN products p 
                ON p.prod_code = bi.prod_code
            where b.bundle_id = ?
        ", [$bundleId]);
        
        $this->showBundles = false;
    }

    public $updateBundleId;
    public $updateStat;

    public function saveStatus($bundleId, $index)
    {
        // Get the new status from the collection
        $newStatus = $this->allBundle[$index]->status;

        // Update the DB
        DB::table('bundles')
            ->where('bundle_id', $bundleId)
            ->update([
                'status' => $newStatus,
                'updated_at' => now(),
            ]);

        session()->flash('success', 'Status updated successfully!');
    }





// public function loadNearExpiryBundles()
// {
//     $owner_id = Auth::guard('owner')->user()->owner_id;

//     $now = Carbon::now();
//     $expiryThreshold = $now->copy()->addMonths(2); 

//     $nearExpiryProducts = collect(DB::select("
//         SELECT 
//             p.prod_code,
//             p.name,
//             SUM(i.stock) AS total_stock,
//             MIN(i.expiration_date) AS nearest_expiry
//         FROM inventory i
//         INNER JOIN products p ON i.prod_code = p.prod_code
//         WHERE i.expiration_date IS NOT NULL
//         AND p.owner_id = ?
//         AND DATE(i.expiration_date) >= CURDATE()
//         AND DATE(i.expiration_date) <= DATE_ADD(CURDATE(), INTERVAL 2 MONTH)
//         GROUP BY p.prod_code, p.name;
//     ", [$owner_id]));

//     $this->suggestedBundles = $nearExpiryProducts->map(function ($product) {
//         $daysToExpire = Carbon::parse($product->nearest_expiry)->diffInDays(now());

//         return [
//             'id' => $product->prod_code,
//             'name' => "Near Expiry Bundle: {$product->name}",
//             'category' => 'Near Expiration',
//             'products' => [
//                 [
//                     'prod_code' => $product->prod_code,
//                     'name' => $product->name,
//                     'stock' => (int) $product->total_stock,   // ✅ FIX
//                     'days_to_expire' => $daysToExpire,        // ✅ FIX
//                     'slow_moving' => false,
//                 ]
//             ],
//             'profit' => 0,
//             'max_discount' => 0,
//         ];
//     })->values()->toArray();
// }




// public function approveSuggestedBundle($bundleId)
// {
    
//     $bundle = collect($this->suggestedBundles)->firstWhere('id', $bundleId);

//     if (!$bundle) return;

//     DB::transaction(function () use ($bundle) {
        
//         $bundleId = DB::table('bundles')->insertGetId([
//             'bundle_code' => 'AUTO-' . $bundle['id'],
//             'bundle_name' => $bundle['name'],
//             'bundle_category' => $bundle['category'],
//             'bundle_type' => 'PROMOTIONAL',
//             'status' => 'active',
//             'owner_id' => Auth::guard('owner')->user()->owner_id,
//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);

        
//         foreach ($bundle['products'] as $prod) {
//             DB::table('bundle_items')->insert([
//                 'bundle_id' => $bundleId,
//                 'prod_code' => $prod['prod_code'],
//                 'inven_code' => null,
//                 'quantity' => 1,
//             ]);
//         }

        
//         DB::table('bundles_setting')->insert([
//             'bundle_id' => $bundleId,
//             'start_date' => now(),
//             'end_date' => now()->addMonths(1),
//             'min_margin' => 0,
//             'discount_percent' => 0,
//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);
//     });

//     session()->flash('message', 'Suggested bundle approved successfully.');

    
//     $this->loadSuggestedBundles();
// }

// public function editSuggestedBundle($bundleId)
// {
    
//     $bundle = collect($this->suggestedBundles)->firstWhere('id', $bundleId);
//     if (!$bundle) return;

//     $this->emit('editSuggestedBundle', $bundle);
// }





}