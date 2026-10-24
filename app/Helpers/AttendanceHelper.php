<?php

namespace App\Helpers;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Collection;
class AttendanceHelper
{
    // Record employee check-in for today
    public static function recordCheckIn(int $employeeId, ?string $notes = null)
    {
        $employee = Employee::findOrFail($employeeId);

        // Inactive employees cannot mark attendance
        if ($employee->status !== 'active') {
            throw new Exception('Inactive employee cannot check in.');
        }

        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('date', $today)
            ->first();

        // Check if already checked in
        if ($attendance && $attendance->check_in) {
            $formattedTime = Carbon::parse($attendance->check_in)->format('h:i A');
            throw new Exception("Already checked in today at {$formattedTime}.");
        }

        $now = Carbon::now();

        if ($attendance) {
            // Update existing record if created as absent earlier
            $attendance->update([
                'check_in' => $now,
                'status' => 'present',
                'notes' => $notes ?? $attendance->notes,
            ]);
            return $attendance->fresh();
        }

        // Create new attendance record
        return Attendance::create([
            'employee_id' => $employeeId,
            'date' => $today,
            'check_in' => $now,
            'status' => 'present',
            'notes' => $notes,
        ]);
    }

    // Record employee check-out for today and calculate total hours
    public static function recordCheckOut(int $employeeId, ?string $notes = null)
    {
        $today = Carbon::today()->toDateString();
        $attendance = Attendance::where('employee_id', $employeeId)
            ->whereDate('date', $today)
            ->first();

        // Must have checked in before checking out
        if (!$attendance || !$attendance->check_in) {
            throw new Exception('No check-in record found for today. Please check in first.');
        }

        $now = Carbon::now();
        $checkInTime = Carbon::parse($attendance->check_in);

        // Calculate hours worked rounded to 2 decimal places
        $totalHours = round($checkInTime->diffInMinutes($now) / 60, 2);

        // Calculate status based on hours worked:
        // >= 8 hours: present
        // >= 4 hours: half_day
        // < 4 hours: absent
        if ($totalHours >= 8) {
            $status = 'present';
        } elseif ($totalHours >= 4) {
            $status = 'half_day';
        } else {
            $status = 'absent';
        }

        $attendance->update([
            'check_out' => $now,
            'total_hours' => $totalHours,
            'status' => $status,
            'notes' => $notes ?? $attendance->notes,
        ]);

        return $attendance->fresh();
    }

    // Get active employees who have not checked in today
    public static function getTodayAbsentEmployees()
    {
        $today = Carbon::today()->toDateString();

        return Employee::with('department')
            ->where('status', 'active')
            ->whereDoesntHave('attendances', function ($query) use ($today) {
                $query->whereDate('date', $today)->whereNotNull('check_in');
            })
            ->get();
    }

    // Get today's attendance summary metrics for dashboard
    public static function getDashboardMetrics()
    {
        $today = Carbon::today()->toDateString();

        $totalEmployees = Employee::count();
        $activeEmployees = Employee::where('status', 'active')->count();

        // Present employees today
        $todayPresentCount = Attendance::whereDate('date', $today)
            ->whereNotNull('check_in')
            ->where('status', 'present')
            ->count();

        // Absent active employees today
        $todayAbsentCount = max(0, $activeEmployees - $todayPresentCount);

        return [
            'total_employees' => $totalEmployees,
            'active_employees' => $activeEmployees,
            'today_present' => $todayPresentCount,
            'today_absent' => $todayAbsentCount,
        ];
    }
}
