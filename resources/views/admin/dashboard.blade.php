@extends('layouts.app')

@section('title', 'Admin Dashboard - AMS')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1">Company Dashboard</h4>
        <p class="text-muted small mb-0">Daily attendance metrics, active workforce, and recent log overview.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Employee
        </a>
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list me-1"></i> All Attendance
        </a>
    </div>
</div>

<!-- Metrics Cards Row -->
<div class="row g-3 mb-4">
    <!-- Total Employees -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em;">Total Employees</span>
                    <span class="badge badge-soft-primary rounded-pill px-2"><i class="fas fa-users"></i></span>
                </div>
                <div class="h2 fw-bold text-dark mb-0">{{ $metrics['total_employees'] }}</div>
                <small class="text-muted" style="font-size: 0.78rem;">Registered in system</small>
            </div>
        </div>
    </div>

    <!-- Active Employees -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em;">Active Staff</span>
                    <span class="badge badge-soft-success rounded-pill px-2"><i class="fas fa-user-check"></i></span>
                </div>
                <div class="h2 fw-bold text-dark mb-0">{{ $metrics['active_employees'] }}</div>
                <small class="text-success" style="font-size: 0.78rem;">Eligible to work</small>
            </div>
        </div>
    </div>

    <!-- Today's Present -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em;">Present Today</span>
                    <span class="badge badge-soft-success rounded-pill px-2"><i class="fas fa-calendar-check"></i></span>
                </div>
                <div class="h2 fw-bold text-success mb-0">{{ $metrics['today_present'] }}</div>
                <small class="text-muted" style="font-size: 0.78rem;">Checked in for today</small>
            </div>
        </div>
    </div>

    <!-- Today's Absent -->
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em;">Absent Today</span>
                    <span class="badge badge-soft-danger rounded-pill px-2"><i class="fas fa-user-xmark"></i></span>
                </div>
                <div class="h2 fw-bold text-danger mb-0">{{ $metrics['today_absent'] }}</div>
                <a href="{{ route('admin.attendance.absent') }}" class="small text-danger text-decoration-none fw-medium" style="font-size: 0.78rem;">
                    View absent list &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Attendance Records -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-dark"><i class="fas fa-clock text-primary me-2"></i>Recent Attendance Activity</span>
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-sm btn-link text-decoration-none">View All &rarr;</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Date</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Hours</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAttendances as $attendance)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $attendance->employee?->name ?? 'N/A' }}</div>
                                        <small class="text-muted">{{ $attendance->employee?->employee_code }}</small>
                                    </td>
                                    <td>{{ $attendance->employee?->department?->name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($attendance->date)->format('d M, Y') }}</td>
                                    <td>{{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('h:i A') : '--' }}</td>
                                    <td>{{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('h:i A') : ($attendance->check_in ? 'Working' : '--') }}</td>
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">No attendance logs available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side: Today's Absentees & Departments -->
    <div class="col-lg-4">
        <!-- Absent Preview -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-semibold text-danger"><i class="fas fa-user-xmark me-2"></i>Absent Today</span>
                <span class="badge badge-soft-danger rounded-pill">{{ $todayAbsentees->count() }}</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($todayAbsentees as $absent)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-0 border-bottom">
                            <div>
                                <div class="fw-medium text-dark">{{ $absent->name }}</div>
                                <small class="text-muted">{{ $absent->department?->name }} ({{ $absent->employee_code }})</small>
                            </div>
                            <span class="badge badge-soft-danger rounded-pill">Absent</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center py-3 text-success border-0">
                            <i class="fas fa-check-circle me-1"></i> All employees present today
                        </li>
                    @endforelse
                </ul>
            </div>
            @if($todayAbsentees->count() > 0)
                <div class="card-footer bg-white text-center py-2 border-0">
                    <a href="{{ route('admin.attendance.absent') }}" class="btn btn-sm btn-outline-danger w-100">
                        View All Absentees
                    </a>
                </div>
            @endif
        </div>

        <!-- Department Breakdown -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom fw-semibold text-dark">
                <i class="fas fa-building text-primary me-2"></i>Department Overview
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($departments as $dept)
                        <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 border-0 border-bottom">
                            <span class="text-secondary small fw-medium">{{ $dept->name }}</span>
                            <span class="badge bg-light text-dark border rounded-pill">{{ $dept->employees_count }}</span>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted border-0">No departments found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
