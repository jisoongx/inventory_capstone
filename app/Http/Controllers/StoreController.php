<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
                DB::raw('SUM(receipt_item.item_quantity * products.selling_price) as total_amount')
            )
            ->groupBy('receipt.receipt_id', 'receipt.receipt_date')
            ->orderBy('receipt.receipt_date', 'desc');
    
        if ($date) {
            $query->whereDate('receipt.receipt_date', $date);
        }
    
        $transactions = $query->get();
    
        return view('store_starttransaction', [
            'transactions' => $transactions,
            'date' => $date,
        ]);
    }
    
}