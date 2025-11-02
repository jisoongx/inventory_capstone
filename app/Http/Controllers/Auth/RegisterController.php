<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Owner;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'firstname'  => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-]+$/'],
            'middlename' => ['nullable', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-]+$/'],
            'lastname'   => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s\-]+$/'],
            'store_name' => ['required', 'string', 'max:255', 'unique:owners,store_name'],
            'email'      => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Check if email already exists in owners or staff
                    $existsInOwners = DB::table('owners')->where('email', $value)->exists();
                    $existsInStaff  = DB::table('staff')->where('email', $value)->exists();

                    if ($existsInOwners || $existsInStaff) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                },
            ],
            'contact' => [
                'nullable',
                'digits:11',
                'starts_with:09',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $existsInOwners = DB::table('owners')->where('contact', $value)->exists();
                        $existsInStaff  = DB::table('staff')->where('contact', $value)->exists();

                        if ($existsInOwners || $existsInStaff) {
                            $fail('The ' . $attribute . ' number has already been taken.');
                        }
                    }
                },
            ],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ], [

            'firstname.regex' => 'First name must only contain letters.',
            'middlename.regex' => 'Middle name must only contain letters.',
            'lastname.regex' => 'Last name must only contain letters.',
            'store_name.unique' => 'This store name is already registered.',
            'email.unique' => 'This email is already taken.',
            'contact.digits' => 'Contact number must be exactly 11 digits.',
            'contact.starts_with' => 'The contact number must start with 09.',
            'password.min'       => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password does not match.',
        ]);



        // ✅ Start database transaction to ensure data integrity
        DB::beginTransaction();
        
        try {
            // Create the owner
            $owner = Owner::create([
                'firstname'     => strtoupper($request->firstname),
                'middlename'    => strtoupper($request->middlename),
                'lastname'      => strtoupper($request->lastname),
                'store_name'    => strtoupper($request->store_name),
                'store_address' => $request->store_address,
                'email'         => $request->email,
                'contact'       => $request->contact,
                'owner_pass'    => Hash::make($request->password),
                'created_on'    => now(),
            ]);

            // ✅ Generate verification token
            $owner->generateVerificationToken();

            // ✅ NEW: Add default categories for the new owner
            $defaultCategories = [
                // Fresh Products Section
                'Vegetables',
                'Fruits',
                'Meat & Poultry',
                'Fish & Seafood',
                'Eggs',
                
                // Grocery Staples
                'Rice & Grains',
                'Noodles & Pasta',
                'Cooking Oil',
                'Condiments & Sauces',
                'Spices & Seasonings',
                'Canned & Preserved Goods',
                'Flour & Baking',
                
                // Beverages
                'Beverages',
                'Coffee & Tea',
                'Milk & Dairy',
                
                // Frozen Section
                'Frozen Foods',
                
                // Snacks & Confectionery
                'Snacks & Chips',
                'Biscuits & Cookies',
                'Candies & Chocolates',
                'Bread & Bakery',
                
                // Personal Care
                'Personal Care & Hygiene',
                'Health & Medicines',
                
                // Household
                'Household Cleaning',
                'Laundry Care',
                'Paper Products',
                
                // Others
                'Baby Care',
                'Pet Supplies',
                'Cigarettes & Tobacco',
                'School & Office Supplies',
            ];

            $categoryInserts = [];
            foreach ($defaultCategories as $category) {
                $categoryInserts[] = [
                    'category' => $category,
                    'owner_id' => $owner->owner_id, // Use the owner_id from the created owner
                ];
            }
            DB::table('categories')->insert($categoryInserts);

            // ✅ NEW: Add default units for the new owner
            $defaultUnits = [
                // Count-based
                'Piece (pc)',
                'Pieces (pcs)',
                'Pack (pack)',
                'Box (box)',
                'Bundle (bundle)',
                'Dozen (doz)',
                'Set (set)',
                'Pair (pair)',
                
                // Weight - Metric
                'Kilogram (kg)',
                'Gram (g)',
                'Milligram (mg)',
                
                // Weight - Imperial
                'Pound (lb)',
                'Ounce (oz)',
                
                // Volume - Metric
                'Liter (L)',
                'Milliliter (mL)',
                'Cubic Centimeter (cc)',
                
                // Volume - Imperial
                'Gallon (gal)',
                'Fluid Ounce (fl oz)',
                
                // Container Types
                'Bottle (btl)',
                'Can (can)',
                'Jar (jar)',
                'Sachet (sachet)',
                'Pouch (pouch)',
                'Bag (bag)',
                'Sack (sack)',
                'Container (cont)',
                'Tub (tub)',
                'Cup (cup)',
                
                // Length
                'Meter (m)',
                'Centimeter (cm)',
                'Inch (in)',
                'Foot (ft)',
                'Yard (yd)',
                
                // Area
                'Square Meter (sq m)',
                
                // Special
                'Roll (roll)',
                'Sheet (sheet)',
                'Bar (bar)',
                'Stick (stick)',
                'Tablet (tab)',
                'Capsule (cap)',
                'Drop (drop)',
            ];

            $unitInserts = [];
            foreach ($defaultUnits as $unit) {
                $unitInserts[] = [
                    'unit' => $unit,
                    'owner_id' => $owner->owner_id, // Use the owner_id from the created owner
                ];
            }
            DB::table('units')->insert($unitInserts);

            // ✅ Commit the transaction if everything is successful
            DB::commit();

            // ✅ Send verification email
            try {
                Mail::to($owner->email)->send(new VerifyEmail($owner));
            } catch (\Exception $e) {
                // Optional: log if email fails (don't rollback because account is created)
                \Log::error('Email verification failed: ' . $e->getMessage());
            }

            // ✅ Redirect user with success message
            return redirect()
                ->route('login')
                ->with('success', 'Registration successful! Please check your email to verify your account.');
                
        } catch (\Exception $e) {
            // ✅ Rollback the transaction if anything fails
            DB::rollBack();
            
            // Log the error for debugging
            \Log::error('Registration failed: ' . $e->getMessage());
            
            // Redirect back with error message
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Registration failed. Please try again.');
        }
    }


    public function verifyEmail($token)
    {
        $owner = Owner::where('verification_token', $token)->first();

        if (!$owner) {
            return redirect()->route('login')->with('error', 'Invalid or expired verification link.');
        }

        $owner->markEmailAsVerified();

        return redirect()->route('login')->with('success', 'Email verified successfully! You may now log in.');
    }

    public function resendVerification()
    {
        $email = session('unverified_email');

        if (!$email) {
            return redirect()->route('login')->with('error', 'Email not found in session.');
        }

        $owner = Owner::where('email', $email)->first();

        if (!$owner) {
            return redirect()->route('login')->with('error', 'Email not found.');
        }

        if ($owner->hasVerifiedEmail()) {
            return redirect()->route('login')->with('info', 'Your email is already verified.');
        }

        // Generate new token if missing
        if (!$owner->verification_token) {
            $owner->generateVerificationToken();
        }

        Mail::to($owner->email)->send(new VerifyEmail($owner));

        return redirect()->route('login')->with('success', 'Verification email resent successfully! Please check your inbox.');
    }
}
