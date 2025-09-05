<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RestockController extends Controller
{
    public function lowStock()
    {
        // Get the logged-in owner's ID
        $ownerId = Auth::guard('owner')->id(); // adjust guard if needed

        // Query inventory and join with products
        $products = DB::table('inventory')
            ->join('products', 'inventory.prod_code', '=', 'products.prod_code')
            ->where('inventory.owner_id', $ownerId)
            ->where('inventory.stock', '<=', 100)
            ->select(
                'inventory.inven_code',     // ✅ add this
                'products.prod_code',       // ✅ keep product code too
                'products.name',
                'products.cost_price',
                'products.selling_price',
                'products.description',
                'inventory.stock',
                'inventory.stock_limit',
                'inventory.batch_number'
            )
            ->get();

        return view('dashboards.owner.restock_suggestion', compact('products'));
    }

    public function finalize(Request $request)
    {
        $ownerId = auth()->guard('owner')->id();

        // Create restock header
        $restockId = DB::table('restock')->insertGetId([
            'owner_id' => $ownerId,
            'restock_created' => now(),
        ]);

        // Loop through selected products
        foreach ($request->products as $invenCode) {
            DB::table('restock_item')->insert([
                'restock_id'    => $restockId,
                'inven_code'    => $invenCode,  // ✅ matches Blade
                'item_quantity' => $request->quantities[$invenCode] ?? 0,
                'item_priority' => $request->priorities[$invenCode] ?? null,
            ]);
        }

        return redirect()->back()->with('success', 'Restock list finalized successfully!');
    }

    public function list()
    {
        $ownerId = auth()->guard('owner')->id();

        $restocks = DB::table('restock')
            ->where('owner_id', $ownerId)
            ->orderByDesc('restock_created')
            ->get();

        $restockItems = DB::table('restock_item')
            ->join('inventory', 'restock_item.inven_code', '=', 'inventory.inven_code')
            ->join('products', 'inventory.prod_code', '=', 'products.prod_code')
            ->select('restock_item.*', 'products.name')
            ->orderByDesc('restock_item.restock_id')
            ->get();

        return view('dashboards.owner.restock_list', compact('restocks', 'restockItems'));
    }
}
