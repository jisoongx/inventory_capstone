<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryOwnerController extends Controller
{
   public function index(Request $request)
    {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;
        $owner_name = $owner->firstname;

        session(['owner_id' => $owner_id]);

        $search = $request->input('search');
        $category = $request->input('category');

        $query = "
            SELECT 
                p.prod_code,
                p.barcode, 
                p.name, 
                p.cost_price, 
                p.selling_price, 
                p.quantity, 
                u.unit,
                c.category
            FROM products p
            JOIN units u ON p.unit_id = u.unit_id
            JOIN categories c ON p.category_id = c.category_id
            WHERE p.owner_id = :owner_id
        ";

        $params = ['owner_id' => $owner_id];

        // Case-insensitive search
        if (!empty($search)) {
            $query .= " AND (LOWER(p.name) LIKE :search_name OR LOWER(p.barcode) LIKE :search_barcode)";
            $params['search_name'] = '%' . strtolower($search) . '%';
            $params['search_barcode'] = '%' . strtolower($search) . '%';
        }

        if (!empty($category)) {
            $query .= " AND p.category_id = :category_id";
            $params['category_id'] = $category;
        }

        $query .= " ORDER BY p.prod_code ASC";

        $products = DB::select($query, $params);

        $categories = DB::select("SELECT category_id, category FROM categories ORDER BY category ASC");
        $units = DB::select("SELECT unit_id, unit FROM units ORDER BY unit ASC"); // ✅ added this

        return view('inventory-owner', compact('owner_name', 'products', 'categories', 'units', 'search', 'category'));
    }




    public function suggest(Request $request)
    {
        $term = $request->query('term');
        $ownerId = session('owner_id');

        if (!$term) {
            return response()->json([]);
        }

        // Check if user is typing a number (assume it's a barcode)
        $isBarcodeSearch = is_numeric($term);

        if ($isBarcodeSearch) {
            $results = DB::table('products')
                ->where('owner_id', $ownerId)
                ->where('barcode', 'LIKE', $term . '%')
                ->pluck('barcode');
        } else {
            $results = DB::table('products')
                ->where('owner_id', $ownerId)
                ->where('name', 'LIKE', $term . '%')
                ->pluck('name');
        }

        return response()->json($results);
    }


    public function checkBarcode(Request $request)
    {
        $barcode = $request->input('barcode');

        // Replace `products` with your actual table name
        $product = DB::table('products')
            ->where('barcode', $barcode)
            ->where('owner_id', session('owner_id')) // adjust if you're using different session key
            ->first();

        if ($product) {
            return response()->json(['exists' => true]);
        } else {
            return response()->json(['exists' => false]);
        }
    }


    public function registerProduct(Request $request)
    {
        $ownerId = session('owner_id');

        $validated = $request->validate([
            'barcode' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'category_id' => 'required|integer',
            'unit_id' => 'required|integer', // ✅ now expect unit_id instead of free text
            'quantity' => 'required|integer|min:1',
            'photo' => 'nullable|image|max:2048'
        ]);

        // Handle photo
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('product_images', 'public');
        }

        DB::table('products')->insert([
            'barcode' => $validated['barcode'],
            'name' => $validated['name'],
            'cost_price' => $validated['cost_price'],
            'selling_price' => $validated['selling_price'],
            'description' => $validated['description'] ?? null,
            'owner_id' => $ownerId,
            'staff_id' => null,
            'category_id' => $validated['category_id'],
            'unit_id' => $validated['unit_id'], // ✅ store directly
            'quantity' => $validated['quantity'],
            'prod_image' => $photoPath,
        ]);

        return response()->json(['success' => true]);
    }





}
