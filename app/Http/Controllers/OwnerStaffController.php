<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Staff;

class OwnerStaffController extends Controller
{

    public function showStaff()
    {
        $ownerId = Auth::guard('owner')->id();
        $staffMembers = Staff::where('owner_id', $ownerId)->paginate(10);
        return view('dashboards.owner.staff_list', compact('staffMembers'));
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
        ActivityLogController::log($description, 'owner',  $user, $request->ip());

        return back()->with('success', 'Staff status updated successfully!');
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
            ActivityLogController::log($description,  'owner', $user, $request->ip());

            return response()->json(['message' => 'Staff details updated successfully!']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }


    public function addStaff(Request $request)
    {
        $ownerId = Auth::guard('owner')->id();
        $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:staff'],
            'contact' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

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

        return redirect()->route('owner.profile')->with('success', 'Staff account created successfully!');
    }
}
