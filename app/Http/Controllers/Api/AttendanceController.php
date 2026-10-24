<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\CheckInRequest;
use App\Http\Requests\Attendance\CheckOutRequest;
use App\Models\Attendance;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    // Mark check-in for employee
    public function checkIn(CheckInRequest $request)
    {
        try {
            $user = $request->user();

            // Determine target employee ID
            if ($user->role === 'employee') {
                $employee = $user->employee;
                if (!$employee) {
                    return ApiResponse::error('Employee profile not associated with this user account.', 404);
                }
                $employeeId = $employee->id;
            } else {
                // Admin marking check-in on behalf of employee
                if (!$request->filled('employee_id')) {
                    return ApiResponse::error('Employee ID is required for admin check-in.', 422);
                }
                $employeeId = (int) $request->employee_id;
            }

            $attendance = AttendanceHelper::recordCheckIn($employeeId, $request->notes);

            return ApiResponse::success($attendance, 'Checked in successfully.', 201);

        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    // Mark check-out for employee and calculate hours
    public function checkOut(CheckOutRequest $request)
    {
        try {
            $user = $request->user();

            // Determine target employee ID
            if ($user->role === 'employee') {
                $employee = $user->employee;
                if (!$employee) {
                    return ApiResponse::error('Employee profile not found.', 404);
                }
                $employeeId = $employee->id;
            } else {
                if (!$request->filled('employee_id')) {
                    return ApiResponse::error('Employee ID is required for admin check-out.', 422);
                }
                $employeeId = (int) $request->employee_id;
            }

            $attendance = AttendanceHelper::recordCheckOut($employeeId, $request->notes);

            return ApiResponse::success($attendance, 'Checked out successfully.', 200);

        } catch (Exception $e) {
            return ApiResponse::error($e->getMessage(), 400);
        }
    }

    // Get attendance history with filters
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            $query = Attendance::with(['employee.department'])->latest('date');

            // Restrict employees to only their own records
            if ($user->role === 'employee') {
                $employee = $user->employee;
                if (!$employee) {
                    return ApiResponse::error('Employee profile not found.', 404);
                }
                $query->where('employee_id', $employee->id);
            } else {
                // Admin filters
                if ($request->filled('employee_id')) {
                    $query->where('employee_id', $request->employee_id);
                }

                if ($request->filled('department_id')) {
                    $deptId = $request->department_id;
                    $query->whereHas('employee', function ($q) use ($deptId) {
                        $q->where('department_id', $deptId);
                    });
                }
            }

            // Date filtering
            if ($request->filled('date')) {
                $query->whereDate('date', $request->date);
            } elseif ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('date', [$request->from_date, $request->to_date]);
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $perPage = (int) $request->input('per_page', 15);
            $attendances = $query->paginate($perPage);

            return ApiResponse::success($attendances, 'Attendance records retrieved successfully.', 200);

        } catch (Exception $e) {
            return ApiResponse::error('Failed to retrieve attendance records.', 500);
        }
    }
}
