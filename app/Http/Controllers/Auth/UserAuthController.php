<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UserLoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserAuthController extends Controller
{
    public function login(UserLoginRequest $request): RedirectResponse
    {
        if (auth()->attempt(['name' => $request->validated('loginname'), 'password' => $request->validated('loginpassword')])) {
            $request->session()->regenerate();
            return redirect()->route('rooms.index');
        }

        return redirect()->route('login');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->validated(),
            'password' => bcrypt($request->validated('password')),
        ]);

        auth()->login($user);
        return redirect()->route('rooms.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
