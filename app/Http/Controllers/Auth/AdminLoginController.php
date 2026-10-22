<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function create()
    {
        return view('admin.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return back()->withErrors(['username' => 'Incorrect username or password.']);
        }

        if (! Auth::user()->hasRole('owner')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['username' => 'Incorrect username or password.']);
        }

        $request->session()->regenerate();
        LoginActivity::create([
            'user_id' => Auth::id(),
            'login_type' => 'owner',
            'logged_in_at' => now(),
        ]);

        return redirect()->intended('/admin/dashboard');
    }
}
