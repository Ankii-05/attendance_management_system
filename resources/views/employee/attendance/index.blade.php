@extends('layouts.app')

@section('title', 'My Attendance History - AMS')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1">Attendance History</h4>
        <p class="text-muted small mb-0">Complete record of your past check-ins, check-outs, and logged hours.</p>
    </div>
    <div>
        <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary">
            &larr; Back to Dashboard
        </a>
    </div>
</div>

<!-- Date Filter Form -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('employee.attendance.index') }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label for="from_date" class="form-label small text-muted mb-1">From Date</label>
                    <input type="date" id="from_date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-4">
                    <label for="to_date" class="form-label small text-muted mb-1">To Date</label>
                    <input type="date" id="to_date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Apply Filter
                    </button>
                    <a href="{{ route('employee.attendance.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Summary Row -->
<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em;">Logged Days</span>
                <div class="h4 fw-bold text-dark mb-0 mt-1">{{ $totalDays }} days</div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <span class="text-muted small text-uppercase fw-semibold" style="letter-spacing: 0.04em;">Total Working Hours</span>
                <div class="h4 fw-bold text-primary mb-0 mt-1">{{ number_format($totalHours, 2) }} hrs</div>
            </div>
        </div>
    </div>
</div>

<!-- Records Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom fw-semibold text-dark">
        Past Attendance Records
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Date</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Hours Worked</th>
                        <th>Status</th>
                        <th>Remarks / Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $record)
                        <tr>
                            <td>{{ $attendances->firstItem() + $index }}</td>
                            <td class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($record->date)->format('d M, Y (D)') }}</td>
                            <td>{{ $record->check_in ? \Carbon\Carbon::parse($record->check_in)->format('h:i A') : '--' }}</td>
                            <td>{{ $record->check_out ? \Carbon\Carbon::parse($record->check_out)->format('h:i A') : ($record->check_in ? 'In Progress' : '--') }}</td>
                            <td><span class="badge bg-light text-dark border">{{ number_format($record->total_hours, 2) }} hrs</span></td>
                            <td>
                                @if($record->status === 'present')
                                    <span class="badge badge-soft-success rounded-pill px-2 py-1">Present</span>
                                @elseif($record->status === 'half_day')
                                    <span class="badge badge-soft-warning rounded-pill px-2 py-1">Half Day</span>
                                @else
                                    <span class="badge badge-soft-danger rounded-pill px-2 py-1">Absent</span>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ $record->notes ?? '-' }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="far fa-calendar-xmark fs-3 d-block mb-2"></i>
                                No attendance records found for this date range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($attendances->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $attendances->links() }}
        </div>
    @endif
</div>
@endsection
