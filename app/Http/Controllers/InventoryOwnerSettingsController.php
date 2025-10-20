<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ActivityLogController;

class InventoryOwnerSettingsController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::guard('owner')->check()) {
            return redirect()->route('login')->with('error', 'Please login first.');
        }

        $owner = Auth::guard('owner')->user();
        $owner_id = $owner->owner_id;

        session(['owner_id' => $owner_id]);

        // Fetch categories and units for this owner
        $categories = DB::table('categories')
            ->where('owner_id', $owner_id)
            ->get();

        $units = DB::table('units')
            ->where('owner_id', $owner_id)
            ->get();

        return view('inventory-owner-settings', compact('categories', 'units'));
    }

    // Store new category
    public function storeCategory(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
        ]);

        $owner_id = session('owner_id');

        DB::table('categories')->insert([
            'category' => $request->category,
            'owner_id' => $owner_id,
        ]);


        $user = auth('owner')->user();
        $ip = $request->ip();

        ActivityLogController::log(
            'Added category: ' . $request->category,
            'owner',
            $user,
            $ip
        );
        return redirect()->route('inventory-owner-settings')->with('success', 'Category added successfully!');
    }

    // Update category
    public function updateCategory(Request $request, $id)
    {
        $request->validate([
            'category' => 'required|string|max:255',
        ]);

        $oldCategory = DB::table('categories')->where('category_id', $id)->value('category');

        DB::table('categories')
            ->where('category_id', $id)
            ->update([
                'category' => $request->category,
            ]);

        $user = auth('owner')->user();
        $ip = $request->ip();

        ActivityLogController::log(
            'Updated category from "' . $oldCategory . '" to "' . $request->category . '"',
            'owner',
            $user,
            $ip
        );

        return redirect()->route('inventory-owner-settings')->with('success', 'Category updated successfully!');
    }

    // Store new unit
    public function storeUnit(Request $request)
    {
        $request->validate([
            'unit' => 'required|string|max:255',
        ]);

        $owner_id = session('owner_id');

        DB::table('units')->insert([
            'unit' => $request->unit,
            'owner_id' => $owner_id,
        ]);

        $user = auth('owner')->user(); // or adjust if using another guard
        $ip = $request->ip();

        ActivityLogController::log(
            'Added unit: ' . $request->unit, // include the unit name
            'owner',
            $user,
            $ip
        );

        return redirect()->route('inventory-owner-settings')->with('success', 'Unit added successfully!');
    }

    // Update unit
    public function updateUnit(Request $request, $id)
    {
        $request->validate([
            'unit' => 'required|string|max:255',
        ]);

        $oldUnit = DB::table('units')->where('unit_id', $id)->value('unit');

        DB::table('units')
            ->where('unit_id', $id)
            ->update([
                'unit' => $request->unit,
            ]);

        $user = auth('owner')->user(); // adjust guard if needed
        $ip = $request->ip();

        ActivityLogController::log(
            'Updated unit from "' . $oldUnit . '" to "' . $request->unit . '"',
            'owner',
            $user,
            $ip
        );

        return redirect()->route('inventory-owner-settings')->with('success', 'Unit updated successfully!');
    }
}
