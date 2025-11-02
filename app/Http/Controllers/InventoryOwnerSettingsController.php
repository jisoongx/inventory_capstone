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
        $categoryName = trim($request->category);

        // Get all existing categories for comparison
        $existingCategories = DB::table('categories')
            ->where('owner_id', $owner_id)
            ->get();

        // Check 1: Exact case-insensitive match
        $exactMatch = DB::table('categories')
            ->where('owner_id', $owner_id)
            ->whereRaw('LOWER(category) = ?', [strtolower($categoryName)])
            ->first();

        if ($exactMatch) {
            return redirect()->route('inventory-owner-settings')
                ->with('error', 'Category already exists: "' . $exactMatch->category . '"');
        }

        // Check 2: Semantic similarity
        $normalizedInput = $this->normalizeName($categoryName);
        $semanticMatch = $this->findSemanticMatch($normalizedInput, $existingCategories, 'category');

        if ($semanticMatch) {
            return redirect()->route('inventory-owner-settings')
                ->with('error', 'Similar category already exists: "' . $semanticMatch . '". Did you mean this one?');
        }

        // Both checks passed - insert new category
        DB::table('categories')->insert([
            'category' => $categoryName,
            'owner_id' => $owner_id,
        ]);

        $user = auth('owner')->user();
        $ip = $request->ip();

        ActivityLogController::log(
            'Added category: ' . $categoryName,
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

        $owner_id = session('owner_id');
        $categoryName = trim($request->category);

        $oldCategory = DB::table('categories')->where('category_id', $id)->value('category');

        // Get all existing categories except the current one
        $existingCategories = DB::table('categories')
            ->where('owner_id', $owner_id)
            ->where('category_id', '!=', $id)
            ->get();

        // Check 1: Exact case-insensitive match
        $exactMatch = DB::table('categories')
            ->where('owner_id', $owner_id)
            ->where('category_id', '!=', $id)
            ->whereRaw('LOWER(category) = ?', [strtolower($categoryName)])
            ->first();

        if ($exactMatch) {
            return redirect()->route('inventory-owner-settings')
                ->with('error', 'Category already exists: "' . $exactMatch->category . '"');
        }

        // Check 2: Semantic similarity
        $normalizedInput = $this->normalizeName($categoryName);
        $semanticMatch = $this->findSemanticMatch($normalizedInput, $existingCategories, 'category');

        if ($semanticMatch) {
            return redirect()->route('inventory-owner-settings')
                ->with('error', 'Similar category already exists: "' . $semanticMatch . '". Did you mean this one?');
        }

        // Both checks passed - update category
        DB::table('categories')
            ->where('category_id', $id)
            ->update([
                'category' => $categoryName,
            ]);

        $user = auth('owner')->user();
        $ip = $request->ip();

        ActivityLogController::log(
            'Updated category from "' . $oldCategory . '" to "' . $categoryName . '"',
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
        $unitName = trim($request->unit);

        // Get all existing units for comparison
        $existingUnits = DB::table('units')
            ->where('owner_id', $owner_id)
            ->get();

        // Check for unit match (exact, semantic, or typo)
        $unitMatch = $this->findUnitMatch($unitName, $existingUnits);

        if ($unitMatch) {
            if ($unitMatch['isExact']) {
                return redirect()->route('inventory-owner-settings')
                    ->with('error', 'Unit already exists: "' . $unitMatch['unit'] . '"');
            } else {
                return redirect()->route('inventory-owner-settings')
                    ->with('error', 'Similar unit already exists: "' . $unitMatch['unit'] . '". Did you mean this one?');
            }
        }

        // Both checks passed - insert new unit
        DB::table('units')->insert([
            'unit' => $unitName,
            'owner_id' => $owner_id,
        ]);

        $user = auth('owner')->user();
        $ip = $request->ip();

        ActivityLogController::log(
            'Added unit: ' . $unitName,
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

        $owner_id = session('owner_id');
        $unitName = trim($request->unit);

        $oldUnit = DB::table('units')->where('unit_id', $id)->value('unit');

        // Get all existing units except the current one
        $existingUnits = DB::table('units')
            ->where('owner_id', $owner_id)
            ->where('unit_id', '!=', $id)
            ->get();

        // Check for unit match (exact, semantic, or typo)
        $unitMatch = $this->findUnitMatch($unitName, $existingUnits);

        if ($unitMatch) {
            if ($unitMatch['isExact']) {
                return redirect()->route('inventory-owner-settings')
                    ->with('error', 'Unit already exists: "' . $unitMatch['unit'] . '"');
            } else {
                return redirect()->route('inventory-owner-settings')
                    ->with('error', 'Similar unit already exists: "' . $unitMatch['unit'] . '". Did you mean this one?');
            }
        }

        // Both checks passed - update unit
        DB::table('units')
            ->where('unit_id', $id)
            ->update([
                'unit' => $unitName,
            ]);

        $user = auth('owner')->user();
        $ip = $request->ip();

        ActivityLogController::log(
            'Updated unit from "' . $oldUnit . '" to "' . $unitName . '"',
            'owner',
            $user,
            $ip
        );

        return redirect()->route('inventory-owner-settings')->with('success', 'Unit updated successfully!');
    }

    // ==================== VALIDATION HELPER METHODS ====================

    public function checkExistingName(Request $request)
    {
        $ownerId = session('owner_id');
        $type = $request->type; // 'category' or 'unit'
        $name = $request->name;
        $excludeValue = $request->excludeValue; // For edit forms - exclude the current value
        
        if (!$ownerId || !$type || !$name) {
            return response()->json(['exists' => false]);
        }
        
        if ($type === 'category') {
            // Get all existing categories except the one being edited (if any)
            $query = DB::table('categories')->where('owner_id', $ownerId);
            
            if ($excludeValue) {
                $query->where('category', '!=', $excludeValue);
            }
            
            $existingCategories = $query->get();
            
            // Check 1: Exact case-insensitive match
            $exactMatch = DB::table('categories')
                ->where('owner_id', $ownerId)
                ->whereRaw('LOWER(category) = ?', [strtolower($name)]);
            
            if ($excludeValue) {
                $exactMatch->where('category', '!=', $excludeValue);
            }
            
            $exactMatchResult = $exactMatch->first();
            
            if ($exactMatchResult) {
                return response()->json([
                    'exists' => true,
                    'existingName' => $exactMatchResult->category,
                    'isExactMatch' => true
                ]);
            }
            
            // Check 2: Semantic match
            $normalizedInput = $this->normalizeName($name);
            $semanticMatch = $this->findSemanticMatch($normalizedInput, $existingCategories, 'category');
            
            if ($semanticMatch) {
                return response()->json([
                    'exists' => true,
                    'existingName' => $semanticMatch,
                    'isExactMatch' => false
                ]);
            }
            
            return response()->json(['exists' => false]);
            
        } else if ($type === 'unit') {
            // Get all existing units except the one being edited (if any)
            $query = DB::table('units')->where('owner_id', $ownerId);
            
            if ($excludeValue) {
                $query->where('unit', '!=', $excludeValue);
            }
            
            $existingUnits = $query->get();
            
            // Check for unit match (exact, semantic, or typo)
            $unitMatchResult = $this->findUnitMatch($name, $existingUnits);
            
            if ($unitMatchResult) {
                return response()->json([
                    'exists' => true,
                    'existingName' => $unitMatchResult['unit'],
                    'isExactMatch' => $unitMatchResult['isExact']
                ]);
            }
            
            return response()->json(['exists' => false]);
        }
        
        return response()->json(['exists' => false]);
    }


    // Normalize name for semantic comparison
    private function normalizeName($name)
    {
        $name = strtolower(trim($name));
        
        // Replace common variations
        $replacements = [
            ' and ' => ' & ',
            ' + ' => ' & ',
            ' with ' => ' & ',
            ' plus ' => ' & ',
            // Remove common filler words
            'the ' => '',
            ' of ' => ' ',
            ' in ' => ' ',
        ];
        
        $name = str_replace(array_keys($replacements), array_values($replacements), $name);
        
        // Remove extra spaces and special characters
        $name = preg_replace('/[^\w&]/', ' ', $name);
        $name = preg_replace('/\s+/', ' ', $name);
        
        return trim($name);
    }

    // Find semantic matches in existing categories
    private function findSemanticMatch($normalizedInput, $existingItems, $column)
    {
        $inputWords = array_filter(explode(' ', $normalizedInput));
        
        // Skip if input is empty
        if (empty($inputWords)) {
            return null;
        }
        
        foreach ($existingItems as $item) {
            $existingName = $item->{$column};
            $normalizedExisting = $this->normalizeName($existingName);
            
            // Check for exact normalized match
            if ($normalizedInput === $normalizedExisting) {
                return $existingName;
            }
            
            // Check if input is a substring of existing category (must start at beginning)
            if (strpos($normalizedExisting, $normalizedInput) === 0) {
                return $existingName;
            }
            
            // Check if existing is a substring of input (must start at beginning)
            if (strpos($normalizedInput, $normalizedExisting) === 0) {
                return $existingName;
            }
            
            $existingWords = array_filter(explode(' ', $normalizedExisting));
            
            // Check if ALL input words have matches in existing category
            $allInputWordsMatched = true;
            $matchedCount = 0;
            
            foreach ($inputWords as $inputWord) {
                if (strlen($inputWord) < 2) continue; // Skip very short words like "&"
                
                $foundMatch = false;
                
                // First check for exact word match
                foreach ($existingWords as $existingWord) {
                    if ($inputWord === $existingWord) {
                        $foundMatch = true;
                        $matchedCount++;
                        break;
                    }
                }
                
                // If no exact match, check for typo similarity
                if (!$foundMatch) {
                    foreach ($existingWords as $existingWord) {
                        if (strlen($existingWord) < 3) continue;
                        
                        // Check if words are very similar (typo detection)
                        $distance = levenshtein($inputWord, $existingWord);
                        $maxLength = max(strlen($inputWord), strlen($existingWord));
                        $similarity = 1 - ($distance / $maxLength);
                        
                        // If words are 70% similar AND at least 4 characters long
                        if ($similarity >= 0.70 && strlen($inputWord) >= 4 && strlen($existingWord) >= 4) {
                            $foundMatch = true;
                            $matchedCount++;
                            break;
                        }
                        
                        // Check if one word contains the other (singular/plural)
                        if (strlen($inputWord) >= 4 && strlen($existingWord) >= 4) {
                            if (strpos($existingWord, $inputWord) !== false || 
                                strpos($inputWord, $existingWord) !== false) {
                                $foundMatch = true;
                                $matchedCount++;
                                break;
                            }
                        }
                    }
                }
                
                // If this input word didn't match anything, category doesn't match
                if (!$foundMatch) {
                    $allInputWordsMatched = false;
                    break;
                }
            }
            
            // Only return match if ALL input words were matched
            if ($allInputWordsMatched && $matchedCount > 0 && count($inputWords) > 1) {
                // Additional check: input should represent significant portion
                $matchRatio = count($inputWords) / count($existingWords);
                
                // Only match if input represents at least 50% of the existing category
                if ($matchRatio >= 0.5) {
                    return $existingName;
                }
            }
            
            // Special case: If input has only 1 word and it matches exactly
            if (count($inputWords) === 1 && $matchedCount === 1) {
                // Only match if the existing category also has 1 word
                if (count($existingWords) === 1) {
                    return $existingName;
                }
            }
        }
        
        return null;
    }

    // Find unit matches considering parenthesis notation AND similarity
    private function findUnitMatch($input, $existingUnits)
    {
        $inputLower = strtolower(trim($input));
        $bestMatch = null;
        
        foreach ($existingUnits as $unit) {
            $existingUnit = $unit->unit;
            $existingUnitLower = strtolower($existingUnit);
            
            // Exact match (case-insensitive)
            if ($inputLower === $existingUnitLower) {
                return ['unit' => $existingUnit, 'isExact' => true];
            }
            
            // Extract the main name and abbreviation from format "Name (abbr)"
            if (preg_match('/^(.+?)\s*\((.+?)\)$/', $existingUnit, $matches)) {
                $unitName = strtolower(trim($matches[1])); // e.g., "bottle"
                $unitAbbr = strtolower(trim($matches[2])); // e.g., "btl"
                
                // Check if input matches the name part exactly
                if ($inputLower === $unitName) {
                    return ['unit' => $existingUnit, 'isExact' => true];
                }
                
                // Check if input matches the abbreviation part exactly
                if ($inputLower === $unitAbbr) {
                    return ['unit' => $existingUnit, 'isExact' => true];
                }
                
                // Check for similarity with the name part
                if (!$bestMatch && $this->isSimilarString($inputLower, $unitName)) {
                    $bestMatch = ['unit' => $existingUnit, 'isExact' => false];
                }
                
                // Check for similarity with the abbreviation
                if (!$bestMatch && $this->isSimilarString($inputLower, $unitAbbr)) {
                    $bestMatch = ['unit' => $existingUnit, 'isExact' => false];
                }
                
                // Check if input is trying to create "Name (abbr)" that already exists
                if (preg_match('/^(.+?)\s*\((.+?)\)$/', $input, $inputMatches)) {
                    $inputName = strtolower(trim($inputMatches[1]));
                    $inputAbbr = strtolower(trim($inputMatches[2]));
                    
                    // Same name or same abbreviation
                    if ($inputName === $unitName || $inputAbbr === $unitAbbr) {
                        return ['unit' => $existingUnit, 'isExact' => true];
                    }
                    
                    // Check for similarity in formatted units
                    if (!$bestMatch && ($this->isSimilarString($inputName, $unitName) || $this->isSimilarString($inputAbbr, $unitAbbr))) {
                        $bestMatch = ['unit' => $existingUnit, 'isExact' => false];
                    }
                }
            } else {
                // Existing unit doesn't have parenthesis format
                // Check if user is trying to add parenthesis version
                if (preg_match('/^(.+?)\s*\((.+?)\)$/', $input, $inputMatches)) {
                    $inputName = strtolower(trim($inputMatches[1]));
                    
                    if ($inputName === $existingUnitLower) {
                        return ['unit' => $existingUnit, 'isExact' => true];
                    }
                    
                    // Check for similarity
                    if (!$bestMatch && $this->isSimilarString($inputName, $existingUnitLower)) {
                        $bestMatch = ['unit' => $existingUnit, 'isExact' => false];
                    }
                } else {
                    // Simple unit vs simple unit similarity check
                    if (!$bestMatch && $this->isSimilarString($inputLower, $existingUnitLower)) {
                        $bestMatch = ['unit' => $existingUnit, 'isExact' => false];
                    }
                }
            }
        }
        
        return $bestMatch;
    }

    // Helper function to check string similarity (for typos)
    private function isSimilarString($str1, $str2)
    {
        // Skip very short strings
        if (strlen($str1) < 3 || strlen($str2) < 3) {
            return false;
        }
        
        // Calculate Levenshtein distance
        $distance = levenshtein($str1, $str2);
        $maxLength = max(strlen($str1), strlen($str2));
        $similarity = 1 - ($distance / $maxLength);
        
        // If strings are 70% similar or more, consider them similar
        return $similarity >= 0.70;
    }
}