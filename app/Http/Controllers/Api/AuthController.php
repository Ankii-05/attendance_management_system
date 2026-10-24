<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Authenticate user and issue API token
    public function login(LoginRequest $request)
    {
        try {
            // Find user by email
            $user = User::where('email', $request->email)->first();

            // Verify credentials
            $passwordValid = $user && Hash::check($request->password, $user->password);

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
                return ApiResponse::error('Invalid email or password.', 401);
            }

            // Check if employee account is active
            if ($user->role === 'employee') {
                $employee = $user->employee;
                if ($employee && $employee->status !== 'active') {
                    return ApiResponse::error('Your account is currently inactive. Please contact your administrator.', 403);
                }
            }

            // Revoke old tokens and create a new Sanctum token
            $user->tokens()->delete();
            $token = $user->createToken('api_token')->plainTextToken;

            // Load employee details if available
            $employeeData = $user->employee ? $user->employee->load('department') : null;

            return ApiResponse::success([
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'employee' => $employeeData,
            ], 'Login successful.', 200);

        } catch (Exception $e) {
            return ApiResponse::error('An error occurred during login. ' . $e->getMessage(), 500);
        }
    }

    // Revoke user token on logout
    public function logout(Request $request)
    {
        try {
            // Delete current token
            $request->user()->currentAccessToken()->delete();

            return ApiResponse::success(null, 'Logged out successfully.', 200);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to log out.', 500);
        }
    }
}
