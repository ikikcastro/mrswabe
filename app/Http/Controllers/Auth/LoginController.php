<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UserLoginRequest;
use App\Models\LoginActivity;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(UserLoginRequest $request)
    {
        if (! Auth::attempt([
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
        ])) {
            return back()->withErrors(['email' => 'Incorrect email or password.']);
        }

        $request->session()->regenerate();
        LoginActivity::create([
            'user_id' => Auth::id(),
            'login_type' => Auth::user()->hasRole('owner') ? 'owner' : 'customer',
            'logged_in_at' => now(),
        ]);

        return redirect()->intended('/rooms');
    }
}
