<?php

namespace App\Http\Controllers\Employee;

use App\Helpers\AttendanceHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\CheckInRequest;
use App\Http\Requests\Attendance\CheckOutRequest;
use App\Models\Attendance;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    // View employee attendance history
    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return redirect()->route('login')
                ->withErrors(['general' => 'Employee profile not linked to this user account.']);
        }

        $query = Attendance::where('employee_id', $employee->id)->latest('date');

        // Optional date filtering
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('month')) {
            $date = Carbon::parse($request->month);
            $query->whereYear('date', $date->year)->whereMonth('date', $date->month);
        }

        $attendances = $query->paginate(15)->withQueryString();

        // Calculate summary
        $totalDays = $attendances->total();
        $totalHours = Attendance::where('employee_id', $employee->id)
            ->when($request->filled('from_date') && $request->filled('to_date'), function ($q) use ($request) {
                $q->whereBetween('date', [$request->from_date, $request->to_date]);
            })
            ->sum('total_hours');

        return view('employee.attendance.index', compact('employee', 'attendances', 'totalDays', 'totalHours'));
    }

    // Handle employee self check-in
    public function checkIn(CheckInRequest $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->withErrors(['general' => 'Employee profile not found.']);
        }

        try {
            $attendance = AttendanceHelper::recordCheckIn($employee->id, $request->notes);

            $time = Carbon::parse($attendance->check_in)->format('h:i A');
            return back()->with('success', "Check-in successful at {$time}! Have a productive day.");

        } catch (Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()]);
        }
    }

    // Handle employee self check-out
    public function checkOut(CheckOutRequest $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->withErrors(['general' => 'Employee profile not found.']);
        }

        try {
            $attendance = AttendanceHelper::recordCheckOut($employee->id, $request->notes);

            $time = Carbon::parse($attendance->check_out)->format('h:i A');

            return back()->with('success', "Check-out successful at {$time}! Total hours: {$attendance->total_hours} hrs.");

        } catch (Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()]);
        }
    }
}
