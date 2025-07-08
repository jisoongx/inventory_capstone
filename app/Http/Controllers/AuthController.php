<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        
        $owner = DB::table('owners')->where('email', $email)->first();

        if ($owner) {
            if ($password === $owner->owner_pass) {

                Session::regenerate();
                
                Session::put('authenticated', true);
                Session::put('owner_id', $owner->owner_id);
                Session::put('owner_name', $owner->firstname . ' ' . $owner->lastname);

                return redirect('/dashboard');
            }
        }

        return back()->with('error', 'Invalid credentials');
    }
}
