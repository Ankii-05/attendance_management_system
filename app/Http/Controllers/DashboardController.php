<?php

namespace App\Http\Controllers;

use App\Helpers\AttendanceHelper;
use App\Models\Attendance;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // Display admin dashboard with summary metrics and recent attendance
    public function adminDashboard(Request $request)
    {
        $metrics = AttendanceHelper::getDashboardMetrics();

        // Recent attendance records (only records where deleted_at is null for both attendance and employee, limit 8)
        $recentAttendances = Attendance::with(['employee.department'])
            ->whereNull('deleted_at')
            ->whereHas('employee', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->latest('date')
            ->latest('id')
            ->take(8)
            ->get();

        // Departments overview
        $departments = Department::withCount('employees')->get();

        // Today's absent employees list for quick preview
        $todayAbsentees = AttendanceHelper::getTodayAbsentEmployees()->take(5);

        return view('admin.dashboard', compact('metrics', 'recentAttendances', 'departments', 'todayAbsentees'));
    }

    // Display employee personal dashboard
    public function employeeDashboard(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee ? $user->employee->load('department') : null;

        $today = Carbon::today()->toDateString();
        $todayAttendance = null;
        $monthlyStats = [
            'present_days' => 0,
            'total_hours' => 0.00,
        ];
        $recentAttendances = collect();

        if ($employee) {
            // Today's attendance
            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->whereDate('date', $today)
                ->first();

            // Current month statistics
            $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
            $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

            $monthRecords = Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('status', 'present')
                ->get();

            $monthlyStats['present_days'] = $monthRecords->count();
            $monthlyStats['total_hours'] = round($monthRecords->sum('total_hours'), 2);

            // Recent attendance history
            $recentAttendances = Attendance::where('employee_id', $employee->id)
                ->latest('date')
                ->take(7)
                ->get();
        }

        return view('employee.dashboard', compact('employee', 'todayAttendance', 'monthlyStats', 'recentAttendances'));
    }
}
