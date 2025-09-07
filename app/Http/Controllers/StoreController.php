<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\ReceiptItem;

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
                DB::raw('SUM(receipt_item.item_quantity * products.cost_price) as total_amount') // Changed to cost_price
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

        if ($product->quantity < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Available quantity: ' . $product->quantity
            ], 400);
        }

        return response()->json([
            'success' => true,
            'product' => [
                'prod_code' => $product->prod_code,
                'barcode' => $product->barcode,
                'name' => $product->name,
                'cost_price' => $product->cost_price, // Changed from selling_price to cost_price
                'available_quantity' => $product->quantity,
                'description' => $product->description
            ],
            'requested_quantity' => $request->quantity,
            'total_amount' => $product->cost_price * $request->quantity // Changed to cost_price
        ]);
    }

    public function startTransaction(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.prod_code' => 'required|exists:products,prod_code',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

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
                $itemTotal = $product->cost_price * $item['quantity']; // Changed to cost_price
                $transactionItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'amount' => $itemTotal
                ];
                $totalAmount += $itemTotal;
                $totalQuantity += $item['quantity'];
            }
        }

        // Get user firstname based on authentication guard
        $user_firstname = null;
        
        // Check if user is logged in as owner
        if (Auth::guard('owner')->check()) {
            $user_firstname = Auth::guard('owner')->user()->firstname;
        } 
        // Check if user is logged in as staff
        elseif (Auth::guard('staff')->check()) {
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

    public function processPayment(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'amount_received' => 'required|numeric|min:0'
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
            // Get proper user IDs based on authentication guards
            $owner_id = null;
            $staff_id = null;
            
            if (Auth::guard('owner')->check()) {
                $owner_id = Auth::guard('owner')->user()->owner_id;
            } elseif (Auth::guard('staff')->check()) {
                $staff_id = Auth::guard('staff')->user()->staff_id;
                // Get owner_id from staff table
                $owner_id = Auth::guard('staff')->user()->owner_id;
            }

            // Create receipt
            $receipt = Receipt::create([
                'receipt_date' => now(),
                'owner_id' => $owner_id ?? 1, // Fallback to 1 if no auth
                'staff_id' => $staff_id  // Can be null if owner is logged in
            ]);

            $totalAmount = 0;

            // Create receipt items and update product quantities
            foreach ($items as $item) {
                $product = Product::find($item['prod_code']);
                
                if (!$product || $product->quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for product: " . ($product->name ?? 'Unknown'));
                }

                // Create receipt item
                ReceiptItem::create([
                    'item_quantity' => $item['quantity'],
                    'prod_code' => $item['prod_code'],
                    'receipt_id' => $receipt->receipt_id
                ]);

                // Update product quantity
                $product->decrement('quantity', $item['quantity']);
                
                $totalAmount += $product->cost_price * $item['quantity']; // Changed to cost_price
            }

            // Clear session
            session()->forget('transaction_items');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction completed successfully!',
                'receipt_id' => $receipt->receipt_id,
                'total_amount' => $totalAmount,
                'amount_received' => $request->amount_received,
                'change' => $request->amount_received - $totalAmount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateReceiptNumber()
    {
        $lastReceipt = Receipt::orderBy('receipt_id', 'desc')->first();
        return $lastReceipt ? $lastReceipt->receipt_id + 1 : 1;
    }
}