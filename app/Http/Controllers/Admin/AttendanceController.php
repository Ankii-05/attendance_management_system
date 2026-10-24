<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class AttendanceController extends Controller
{
    // Display attendance records with filters and Yajra DataTable
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Attendance::with(['employee.department'])
                ->select('attendance.*');

            // Filter by employee
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            // Filter by department
            if ($request->filled('department_id')) {
                $deptId = $request->department_id;
                $query->whereHas('employee', function ($q) use ($deptId) {
                    $q->where('department_id', $deptId);
                });
            }

            // Filter by date or date range
            if ($request->filled('date')) {
                $query->whereDate('date', $request->date);
            } elseif ($request->filled('from_date') && $request->filled('to_date')) {
                $query->whereBetween('date', [$request->from_date, $request->to_date]);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::eloquent($query)
                ->addColumn('employee_code', function ($record) {
                    return $record->employee ? e($record->employee->employee_code) : 'N/A';
                })
                ->addColumn('employee_name', function ($record) {
                    return $record->employee ? e($record->employee->name) : 'N/A';
                })
                ->addColumn('department', function ($record) {
                    return $record->employee && $record->employee->department 
                        ? e($record->employee->department->name) 
                        : 'N/A';
                })
                ->addColumn('date_formatted', function ($record) {
                    return $record->date ? Carbon::parse($record->date)->format('d M, Y') : 'N/A';
                })
                ->addColumn('check_in_formatted', function ($record) {
                    return $record->check_in ? Carbon::parse($record->check_in)->format('h:i A') : '<span class="text-muted">--</span>';
                })
                ->addColumn('check_out_formatted', function ($record) {
                    return $record->check_out ? Carbon::parse($record->check_out)->format('h:i A') : '<span class="text-muted">--</span>';
                })
                ->addColumn('hours_badge', function ($record) {
                    return '<span class="badge bg-light text-dark border px-2 py-1">' . number_format($record->total_hours, 2) . ' hrs</span>';
                })
                ->addColumn('status_badge', function ($record) {
                    if ($record->status === 'present') {
                        return '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="fas fa-check-circle me-1"></i>Present</span>';
                    } elseif ($record->status === 'half_day') {
                        return '<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1"><i class="fas fa-clock me-1"></i>Half Day</span>';
                    }
                    return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1"><i class="fas fa-times-circle me-1"></i>Absent</span>';
                })
                ->rawColumns(['check_in_formatted', 'check_out_formatted', 'hours_badge', 'status_badge'])
                ->make(true);
        }

        // Calculate total stats for the view
        $employees = Employee::where('status', 'active')->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        
        // Default today's metrics
        $metrics = AttendanceHelper::getDashboardMetrics();

        return view('admin.attendance.index', compact('employees', 'departments', 'metrics'));
    }

    // View list of employees who are absent today
    public function absentEmployees()
    {
        $absentees = AttendanceHelper::getTodayAbsentEmployees();
        $todayFormatted = Carbon::today()->format('l, d F Y');

        return view('admin.attendance.absent', compact('absentees', 'todayFormatted'));
    }
}
