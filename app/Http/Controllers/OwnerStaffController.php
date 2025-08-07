<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Staff; // Make sure to import the Staff model

class OwnerStaffController extends Controller
{
    /**
     * Show the form for creating a new staff member.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get the authenticated owner's ID
        $ownerId = Auth::guard('owner')->id();

        // Fetch staff members associated with the current owner, with pagination
        $staffMembers = Staff::where('owner_id', $ownerId)->paginate(10); // Adjust pagination limit as needed

        // Return the view, passing the staff data
        return view('dashboards.owner.staff_list', compact('staffMembers'));
    }
    // In OwnerStaffController.php
    public function updateStatus(Request $request, Staff $staff)
    {
        $request->validate([
            'status' => ['required', 'string', 'in:Active,Inactive'],
        ]);

        $staff->status = $request->status;
        $staff->save();

        return back()->with('success', 'Staff status updated successfully!');
    }

 

   

    public function update(Request $request, Staff $staff)
    {
        // Ensure the authenticated owner owns this staff member
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

            return response()->json(['message' => 'Staff details updated successfully!']);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    // In OwnerStaffController.php
    
    /**
     * Store a newly created staff member in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Get the authenticated owner's ID
        $ownerId = Auth::guard('owner')->id();

        // Validate the incoming request data for staff creation
        $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'middlename' => ['nullable', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:staff'], // Ensure email is unique in staff table
            'contact' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // 'password' is the name of the input field
        ]);

        // Create the new staff member
        $staff = Staff::create([
            'owner_id' => $ownerId, // Link staff to the current owner
            'firstname' => $request->firstname,
            'middlename' => $request->middlename,
            'lastname' => $request->lastname,
            'email' => $request->email,
            'contact' => $request->contact,
            'staff_pass' => Hash::make($request->password), // CHANGED: Use 'staff_pass' for the password column
            // REMOVED: 'status' => 'active', as per your feedback that staff has no status field
            // Add any other fields relevant to your Staff model here if they exist and are fillable
        ]);

        return redirect()->route('owner.profile')->with('success', 'Staff account created successfully!');
    }

    public function destroy($id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            return redirect()->back()->withErrors(['Staff not found.']);
        }

        try {
            $staff->delete();
            return redirect()->back()->with('success', 'Staff deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['Error deleting staff: ' . $e->getMessage()]);
        }
    }

}
