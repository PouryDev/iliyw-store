<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string', // Can be instagram_id or phone
            'password' => 'required|string',
        ]);

        // Find user by instagram_id or phone
        $user = User::where('instagram_id', $request->username)
                   ->orWhere('phone', $request->username)
                   ->first();

        // Check if user exists and password is correct
        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'user' => $user
                ]);
            }
            
            return redirect()->intended('/');
        }

        $errorMessage = 'آیدی اینستاگرام، شماره تلفن یا رمز عبور نادرست است.';
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage
            ], 422);
        }

        return back()->withErrors(['username' => $errorMessage])->onlyInput('username');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'instagram_id' => 'nullable|string|max:255|unique:users,instagram_id',
            'phone' => 'required|string|max:255|unique:users,phone',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'instagram_id' => filled($data['instagram_id'] ?? null) ? $data['instagram_id'] : null,
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'user' => $user
            ], 201);
        }
        
        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}


