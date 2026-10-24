<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    // List employees with filters and search
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $query = Employee::with('department')->latest();

            // Search by name, email, or employee code
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('employee_code', 'like', "%{$search}%");
                });
            }

            // Filter by department
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $perPage = (int) $request->input('per_page', 10);
            $employees = $query->paginate($perPage);

            return ApiResponse::success($employees, 'Employees retrieved successfully.', 200);

        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve employees. ' . $e->getMessage(), 500);
        }
    }

    // Get specific employee details
    public function show(Request $request, int|string $id)
    {
        try {
            $user = $request->user();

            // Authorization: Employee cannot view other employees' sensitive profile
            if ($user->role === 'employee') {
                $myEmployee = $user->employee;
                if (!$myEmployee || $myEmployee->id != $id) {
                    return ApiResponse::error('Unauthorized. You can only view your own profile.', 403);
                }
            }

            $employee = Employee::with(['department', 'user'])->find($id);

            if (!$employee) {
                return ApiResponse::error('Employee not found.', 404);
            }

            return ApiResponse::success($employee, 'Employee details retrieved successfully.', 200);

        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve employee details.', 500);
        }
    }

    // Soft delete employee via API
    public function destroy(Request $request, int|string $id)
    {
        try {
            $user = $request->user();
            if ($user->role !== 'admin') {
                return ApiResponse::error('Unauthorized. Only administrators can delete employees.', 403);
            }

            $employee = Employee::find($id);
            if (!$employee) {
                return ApiResponse::error('Employee not found.', 404);
            }

            if ($employee->user) {
                $employee->user->delete();
            }
            $employee->delete();

            return ApiResponse::success(null, 'Employee soft deleted successfully.');

        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete employee: ' . $e->getMessage(), 500);
        }
    }
}
