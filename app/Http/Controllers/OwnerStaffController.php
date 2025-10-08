<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Staff;

class OwnerStaffController extends Controller
{

    public function showStaff()
    {
        $ownerId = Auth::guard('owner')->id();
        $staffMembers = Staff::where('owner_id', $ownerId)->get(); // Fetch all without pagination
        return view('dashboards.owner.staff_list', compact('staffMembers'));
    }


    public function filter(Request $request)
    {
        $query = $request->get('search', '');
        $status = $request->get('status', '');

        $staffMembers = \App\Models\Staff::query()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('firstname', 'like', "%{$query}%")
                        ->orWhere('middlename', 'like', "%{$query}%")
                        ->orWhere('lastname', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->orderBy('firstname')
            ->get();

        return response()->json(['staffMembers' => $staffMembers]);
    }


    public function updateStatus(Request $request, Staff $staff)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:Active,Deactivated'],
        ]);

        $staff->status = $request->status;
        $staff->save();

        $staffName = "{$staff->firstname} {$staff->lastname}";
        $user = Auth::guard('owner')->user();
        $description = "Updated status of staff ({$staffName}) to {$staff->status}";
        ActivityLogController::log($description, 'owner', $user, $request->ip());

        $message = $staff->status === 'Active'
            ? 'Staff has been activated successfully!'
            : 'Staff has been deactivated successfully!';

        return response()->json([
            'message' => $message,
            'staff' => $staff
        ]);
    }




    public function updateStaffInfo(Request $request, Staff $staff)
    {
        if (Auth::guard('owner')->id() !== $staff->owner_id) {
            return response()->json(['error' => 'Unauthorized action.'], 403);
        }

        try {
            $request->validate([
                'firstname' => ['required', 'string', 'max:255'],
                'middlename' => ['nullable', 'string', 'max:255'],
                'lastname' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:staff,email,' . $staff->staff_id . ',staff_id'],
                'contact' => ['nullable', 'string', 'max:20'],
            ]);

            $staff->update($request->only(['firstname', 'middlename', 'lastname', 'email', 'contact']));

            $staffName = "{$staff->firstname} {$staff->lastname}";
            $user = Auth::guard('owner')->user();
            $description = "Updated staff ({$staffName}) details";
            ActivityLogController::log($description, 'owner', $user, $request->ip());

            return response()->json([
                'message' => 'Staff details updated successfully!',
                'staff' => $staff
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }



    public function addStaff(Request $request)
    {
        $ownerId = Auth::guard('owner')->id();

        $validator = Validator::make($request->all(), [
            'firstname'  => ['required', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'lastname'   => ['required', 'string', 'max:255'],
            'email'      => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $existsInStaff  = DB::table('staff')->where('email', $value)->exists();
                    $existsInOwners = DB::table('owners')->where('email', $value)->exists();
                    if ($existsInStaff || $existsInOwners) {
                        $fail('The ' . $attribute . ' has already been taken.');
                    }
                },
            ],
            'contact' => [
                'nullable',
                'string',
                'regex:/^09[0-9]{9}$/', // Must start with 09 and have 11 digits
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);


        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $staff = Staff::create([
            'owner_id' => $ownerId,
            'firstname'  => strtoupper($request->firstname),
            'middlename' => strtoupper($request->middlename),
            'lastname'   => strtoupper($request->lastname),
            'email' => $request->email,
            'contact' => $request->contact,
            'staff_pass' => Hash::make($request->password)
        ]);

        $user = Auth::guard('owner')->user();
        ActivityLogController::log('Created new staff account', 'owner', $user, $request->ip());

        if ($request->ajax()) {
            return response()->json([
                'success' => 'Staff created successfully!',
                'old' => [
                    'firstname' => $request->firstname,
                    'middlename' => $request->middlename,
                    'lastname' => $request->lastname,
                    'email' => $request->email,
                    'contact' => $request->contact
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Staff created successfully!');
    }
}
