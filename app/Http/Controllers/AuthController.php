<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return inertia('auth/Login');
    }

    public function showRegisterForm()
    {
        return inertia('auth/Register');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => 'Email atau Password salah.',
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Debug log
        Log::info('User registered successfully', [
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email
        ]);

        Auth::login($user);

        return redirect('/dashboard');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        Log::info('User logout initiated', [
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User logout completed, redirecting to login');

        return redirect('/login');
    }
}
