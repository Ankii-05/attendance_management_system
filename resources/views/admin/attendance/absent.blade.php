@extends('layouts.app')

@section('title', "Today's Absent Employees - AMS")

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1">Today's Absent Employees</h4>
        <p class="text-muted small mb-0">Active staff members who have not checked in for today ({{ $todayFormatted }}).</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
            &larr; Back to Attendance
        </a>
    </div>
</div>

<!-- Absent Alert Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4 d-flex justify-content-between align-items-center bg-light rounded">
        <div>
            <h5 class="fw-bold text-dark mb-1">
                <i class="fas fa-user-xmark text-danger me-2"></i>{{ $absentees->count() }} Employees Absent
            </h5>
            <small class="text-muted">Date: <strong>{{ $todayFormatted }}</strong></small>
        </div>
        <span class="badge badge-soft-danger px-3 py-2 rounded-pill fs-6 fw-semibold">
            {{ $absentees->count() }} Absent Today
        </span>
    </div>
</div>

<!-- Absentees Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom fw-semibold text-dark">
        Absent Staff Members List
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Employee Code</th>
                        <th>Full Name</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absentees as $index => $employee)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $employee->employee_code }}</span></td>
                            <td class="fw-semibold text-dark">
                                <a href="{{ route('admin.employees.show', $employee->id) }}" class="text-decoration-none text-dark">
                                    {{ $employee->name }}
                                </a>
                            </td>
                            <td>{{ $employee->department?->name ?? 'N/A' }}</td>
                            <td>{{ $employee->designation }}</td>
                            <td>
                                <a href="tel:{{ $employee->mobile_number }}" class="text-decoration-none text-muted">
                                    <i class="fas fa-phone me-1 text-primary"></i>{{ $employee->mobile_number }}
                                </a>
                            </td>
                            <td>
                                <a href="mailto:{{ $employee->email }}" class="text-decoration-none text-muted">
                                    <i class="fas fa-envelope me-1 text-primary"></i>{{ $employee->email }}
                                </a>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-soft-danger rounded-pill px-2 py-1">
                                    Absent
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-circle-check text-success fs-3 d-block mb-2"></i>
                                <span class="fw-medium text-dark">All active employees are present today.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
