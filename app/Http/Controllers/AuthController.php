<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthController\LoginRequest;
use App\Http\Requests\AuthController\LogoutRequest;
use App\Http\Requests\AuthController\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Authentication Controller
 *
 * Responsible for handling:
 * - Login
 * - Registration
 * - Logout
 *
 * This controller delegates validation to FormRequest classes
 * for cleaner and more maintainable code.
 */
class AuthController extends Controller
{
    /**
     * Render the login page.
     */
    public function showLoginForm()
    {
        return inertia('auth/Login');
    }

    /**
     * Render the registration page.
     */
    public function showRegisterForm()
    {
        return inertia('auth/Register');
    }

    /**
     * Handle the login attempt.
     *
     * @param LoginRequest $request
     *      Validated request containing email, password, remember
     */
    public function login(LoginRequest $request)
    {   
        // Debug
        Log::info('Login Attempt', [
            'session_id' => session()->getId(),
            'session_started' => session()->isStarted(),
            'has_xsrf_cookie' => $request->hasCookie('XSRF-TOKEN'),
            'xsrf_from_cookie' => $request->cookie('XSRF-TOKEN'),
            'xsrf_from_header' => $request->header('X-XSRF-TOKEN'),
            'csrf_from_session' => session()->token(),
        ]);
        // Attempt authentication
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {

            // Regenerate session for security
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        // Authentication failed
        throw ValidationException::withMessages([
            'email' => 'Email atau Password salah.',
        ]);
    }

    /**
     * Handle new user registration.
     *
     * @param RegisterRequest $request
     *      Validated registration fields
     */
    public function register(RegisterRequest $request)
    {
        // Create user
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Log success (non-sensitive)
        Log::info('User registered successfully', [
            'user_id' => $user->id,
            'name'    => $user->name,
            'email'   => $user->email
        ]);

        // Auto-login
        Auth::login($user);

        request()->session()->regenerate();

        return redirect('/dashboard');
    }
    
    /**
     * Handle logout process.
     *
     * @param LogoutRequest $request
     *      Validated logout request (if needed)
     */
    public function logout(LogoutRequest $request)
    {
        $user = Auth::user();

        // Log pre-logout info
        Log::info('User logout initiated', [
            'user_id'    => $user?->id,
            'user_email' => $user?->email,
        ]);

        // End authentication
        Auth::logout();

        // Invalidate old session & regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User logout completed, redirecting to login');

        return redirect('/login');
    }
}
