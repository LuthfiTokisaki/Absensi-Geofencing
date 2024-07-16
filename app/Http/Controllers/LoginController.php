<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['nik', 'password']);

        if (Auth::attempt($credentials)) {
            // Authentication successful, redirect to dashboard
            return redirect()->intended(route('dashboard'));
        } else {
            // Authentication failed, redirect back to login page with error message
            return redirect()->back()->withErrors(['nik' => 'Invalid NIK or password']);
        }
    }
}