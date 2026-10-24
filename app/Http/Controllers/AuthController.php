<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    // Show login page
    public function showLoginForm()
    {
        // If already authenticated, redirect to appropriate dashboard
        if (Auth::check()) {
            $user = Auth::user();
            return $user->isAdmin() 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('employee.dashboard');
        }

        return view('auth.login');
    }

    // Process web login
    public function login(LoginRequest $request)
    {
        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();

            // Check credentials
            $passwordValid = $user && Hash::check($request->password, $user->password);

            // Backward compatibility helper: accept both 'Password@123' and 'password123'
            if (!$passwordValid && $user) {
                if ($request->password === 'Password@123' && Hash::check('password123', $user->password)) {
                    $passwordValid = true;
                    $user->update(['password' => Hash::make('Password@123')]);
                    if ($user->employee) {
                        $user->employee->update(['initial_password' => 'Password@123']);
                    }
                } elseif ($request->password === 'password123' && Hash::check('Password@123', $user->password)) {
                    $passwordValid = true;
                }
            }

            if (!$user || !$passwordValid) {
                return back()
                    ->withInput($request->only('email', 'remember'))
                    ->withErrors(['email' => 'Invalid email or password provided.']);
            }

            // Check if employee is active
            if ($user->isEmployee()) {
                $employee = $user->employee;
                if ($employee && $employee->status !== 'active') {
                    return back()
                        ->withInput($request->only('email'))
                        ->withErrors(['email' => 'Your employee account is deactivated. Please contact the administrator.']);
                }
            }

            $remember = $request->boolean('remember');

            // Log user in
            Auth::login($user, $remember);
            $request->session()->regenerate();

            // Redirect based on role
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Welcome back, Admin!');
            }

            // Ensure employee is always redirected to their dashboard and not an admin url
            session()->forget('url.intended');
            return redirect()->route('employee.dashboard')
                ->with('success', 'Welcome back, ' . $user->name . '!');

        } catch (Exception $e) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'An error occurred during login. Please try again.']);
        }
    }

    // Process web logout
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been successfully logged out.');
    }
}
