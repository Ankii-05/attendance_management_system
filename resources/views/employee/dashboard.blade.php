@extends('layouts.app')

@section('title', 'My Dashboard - AMS')

@section('content')
<!-- Welcome & Date Banner -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-2">
    <div>
        <h4 class="fw-bold text-dark mb-1">Welcome back, {{ auth()->user()->name }}!</h4>
        <p class="text-muted small mb-0">Track your daily punches and review your monthly attendance summary.</p>
    </div>
    <div>
        <span class="badge bg-white text-dark border px-3 py-2 fw-normal shadow-sm">
            <i class="far fa-calendar me-1 text-primary"></i> {{ \Carbon\Carbon::today()->format('l, d M Y') }}
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Today's Attendance Hero Action Card -->
    <div class="col-lg-7">
        <div class="card shadow-sm h-100 border-0">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                <span class="fw-semibold text-dark"><i class="fas fa-fingerprint text-primary me-2"></i>Daily Attendance Punch</span>
                <!-- Live Digital Clock -->
                <span id="liveClock" class="text-muted small fw-mono">--:--:--</span>
            </div>
            <div class="card-body p-4 text-center">
                @if(!$todayAttendance || !$todayAttendance->check_in)
                    <!-- Not Checked In Yet -->
                    <div class="py-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle border mb-3" style="width: 72px; height: 72px;">
                            <i class="fas fa-right-to-bracket text-secondary fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">You are not checked in yet</h5>
                        <p class="text-muted small mb-4">Official work duration: <strong>8 Hours</strong>. Punch in to start your work session for today.</p>

                        <!-- Employee Self Check-In Form -->
                        <form method="POST" action="{{ route('employee.attendance.check-in') }}" class="w-75 mx-auto">
                            @csrf
                            <div class="input-group mb-3">
                                <input type="text" name="notes" class="form-control" placeholder="Optional check-in note (e.g. Work from office)">
                                <button type="submit" class="btn btn-primary px-4 fw-medium">
                                    <i class="fas fa-arrow-right-to-bracket me-1"></i> Punch Check-In
                                </button>
                            </div>
                        </form>
                    </div>
                @elseif($todayAttendance->check_in && !$todayAttendance->check_out)
                    <!-- Checked In, Currently Working -->
                    <div class="py-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle rounded-circle border border-success-subtle mb-3" style="width: 72px; height: 72px;">
                            <i class="fas fa-user-check text-success fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Currently On Duty</h5>
                        <p class="text-muted small mb-1">
                            Punched In at: <strong class="text-dark">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</strong>
                        </p>
                        <p class="text-muted small mb-4" style="font-size: 0.8rem;">
                            <i class="far fa-clock text-primary me-1"></i> Standard Shift: <strong>8 Hours</strong> (&lt; 4h: Absent &bull; 4-8h: Half Day &bull; &ge; 8h: Present)
                        </p>

                        <!-- Employee Self Check-Out Form with SweetAlert confirmation -->
                        <form method="POST" 
                              action="{{ route('employee.attendance.check-out') }}" 
                              class="w-75 mx-auto"
                              data-confirm="Are you sure you want to Check-Out now?"
                              data-confirm-btn="Yes, Check Out"
                              data-confirm-color="#e11d48"
                              data-confirm-icon="warning">
                            @csrf
                            <div class="input-group mb-2">
                                <input type="text" name="notes" class="form-control" placeholder="Optional check-out summary note...">
                                <button type="submit" class="btn btn-danger px-4 fw-medium">
                                    <i class="fas fa-arrow-right-from-bracket me-1"></i> Punch Check-Out
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Day Completed -->
                    <div class="py-3">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle border mb-3" style="width: 72px; height: 72px;">
                            <i class="fas fa-circle-check text-primary fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">Today's Shift Completed</h5>
                        <p class="text-muted small mb-3">You have punched out for today.</p>

                        <div class="row g-2 justify-content-center max-w-md mx-auto mb-3" style="max-width: 480px;">
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Check-In</small>
                                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($todayAttendance->check_in)->format('h:i A') }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Check-Out</small>
                                    <span class="fw-semibold">{{ \Carbon\Carbon::parse($todayAttendance->check_out)->format('h:i A') }}</span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 border rounded bg-light">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Total Hours</small>
                                    <span class="fw-semibold text-primary">{{ number_format($todayAttendance->total_hours, 2) }} hrs</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            @if($todayAttendance->status === 'present')
                                <span class="badge badge-soft-success px-3 py-2 rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i> Attendance Status: Present
                                </span>
                            @elseif($todayAttendance->status === 'half_day')
                                <span class="badge badge-soft-warning px-3 py-2 rounded-pill">
                                    <i class="fas fa-clock me-1"></i> Attendance Status: Half Day
                                </span>
                            @else
                                <span class="badge badge-soft-danger px-3 py-2 rounded-pill">
                                    <i class="fas fa-times-circle me-1"></i> Attendance Status: Absent
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Monthly Statistics & Profile Cards -->
    <div class="col-lg-5">
        <!-- Monthly Stats -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="fas fa-chart-simple text-primary me-2"></i>{{ \Carbon\Carbon::now()->format('F Y') }} Summary
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light text-center">
                            <div class="h3 fw-bold text-dark mb-0">{{ $monthlyStats['present_days'] }}</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.75rem;">Days Present</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 border rounded bg-light text-center">
                            <div class="h3 fw-bold text-primary mb-0">{{ $monthlyStats['total_hours'] }}</div>
                            <small class="text-muted text-uppercase" style="font-size: 0.75rem;">Total Hours</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom fw-semibold">
                <i class="fas fa-id-badge text-primary me-2"></i>My Profile
            </div>
            <div class="card-body">
                @if($employee)
                    <div class="row g-2 small">
                        <div class="col-6">
                            <span class="text-muted d-block">Employee ID</span>
                            <span class="fw-semibold">{{ $employee->employee_code }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Department</span>
                            <span class="fw-semibold">{{ $employee->department?->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Designation</span>
                            <span class="fw-semibold">{{ $employee->designation }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Joining Date</span>
                            <span class="fw-semibold">{{ $employee->joining_date?->format('d M, Y') ?? 'N/A' }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Attendance Table -->
<div class="card shadow-sm mt-4 border-0">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <span class="fw-semibold text-dark"><i class="fas fa-list-check text-primary me-2"></i>Recent Attendance History</span>
        <a href="{{ route('employee.attendance.index') }}" class="btn btn-sm btn-link text-decoration-none">Full History &rarr;</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttendances as $attendance)
                        <tr>
                            <td class="fw-semibold">{{ \Carbon\Carbon::parse($attendance->date)->format('d M, Y') }}</td>
                            <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : '--' }}</td>
                            <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : ($attendance->check_in ? 'In Progress' : '--') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ number_format($attendance->total_hours, 2) }} hrs</span></td>
                            <td>
                                @if($attendance->status === 'present')
                                    <span class="badge badge-soft-success rounded-pill px-2 py-1">Present</span>
                                @elseif($attendance->status === 'half_day')
                                    <span class="badge badge-soft-warning rounded-pill px-2 py-1">Half Day</span>
                                @else
                                    <span class="badge badge-soft-danger rounded-pill px-2 py-1">Absent</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $attendance->notes ?? '-' }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No attendance logs found yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Live digital clock display
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        $('#liveClock').text(timeStr);
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>
@endsection
