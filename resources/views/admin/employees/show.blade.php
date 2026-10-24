@extends('layouts.app')

@section('title', 'Employee Profile - ' . $employee->name)

@section('content')
<!-- Header & Navigation -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0 text-gray-800">Employee Details</h3>
        <p class="text-muted small mb-0">Detailed view and attendance records of {{ $employee->name }}.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
        <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit Employee
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Employee Profile Card -->
    <div class="col-lg-4">
        <div class="card shadow-sm text-center p-3 mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                        <i class="fas fa-user"></i>
                    </span>
                </div>
                <h4 class="fw-bold mb-1">{{ $employee->name }}</h4>
                <p class="text-muted mb-2">{{ $employee->designation }}</p>
                
                <div class="mb-3">
                    @if($employee->status === 'active')
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                            <i class="fas fa-check-circle me-1"></i> Active Employee
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1">
                            <i class="fas fa-times-circle me-1"></i> Inactive Employee
                        </span>
                    @endif
                </div>

                <hr>

                <!-- Information List -->
                <ul class="list-group list-group-flush text-start small">
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Employee Code:</span>
                        <span class="fw-semibold">{{ $employee->employee_code }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Department:</span>
                        <span class="fw-semibold">{{ $employee->department?->name ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Email:</span>
                        <span class="fw-semibold">{{ $employee->email }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Mobile:</span>
                        <span class="fw-semibold">{{ $employee->mobile_number }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span class="text-muted">Joining Date:</span>
                        <span class="fw-semibold">{{ $employee->joining_date?->format('d M, Y') ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 bg-light-subtle p-2 rounded mt-2 border">
                        <div>
                            <span class="text-muted d-block" style="font-size: 0.75rem;">Account Password:</span>
                            <span id="passwordText" class="fw-bold font-monospace text-dark" data-password="{{ $employee->initial_password ?? 'Password@123' }}">••••••••</span>
                        </div>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" id="togglePasswordVisibilityBtn" title="Show/Hide Password">
                                <i class="fas fa-eye text-secondary" id="passwordEyeIcon"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light border py-1 px-2" id="copyPasswordBtn" title="Copy Password">
                                <i class="fas fa-copy text-secondary"></i>
                            </button>
                        </div>
                    </li>
                </ul>

                <hr>

                <!-- Toggle status form with SweetAlert confirmation -->
                <form action="{{ route('admin.employees.toggle-status', $employee->id) }}" 
                      method="POST" 
                      data-confirm="Are you sure you want to {{ $employee->status === 'active' ? 'deactivate' : 'activate' }} {{ $employee->name }}?"
                      data-confirm-btn="{{ $employee->status === 'active' ? 'Yes, Deactivate' : 'Yes, Activate' }}"
                      data-confirm-color="{{ $employee->status === 'active' ? '#e11d48' : '#16a34a' }}"
                      data-confirm-icon="question">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-{{ $employee->status === 'active' ? 'warning' : 'success' }} w-100 btn-sm">
                        <i class="fas fa-{{ $employee->status === 'active' ? 'ban' : 'check' }} me-1"></i>
                        {{ $employee->status === 'active' ? 'Deactivate Employee' : 'Activate Employee' }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Attendance Performance Summary -->
        <div class="card shadow-sm">
            <div class="card-header bg-white fw-bold">
                <i class="fas fa-chart-pie text-primary me-2"></i>Attendance Stats
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Total Present Days:</span>
                    <span class="fw-bold h5 mb-0 text-success">{{ $totalPresent }} days</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Total Hours Logged:</span>
                    <span class="fw-bold h5 mb-0 text-primary">{{ $totalHours }} hrs</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Attendance History -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span><i class="fas fa-calendar-alt text-primary me-2"></i>Attendance History</span>
                <span class="badge bg-light text-dark border">{{ $attendances->total() }} total records</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Total Hours</th>
                                <th>Status</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $att)
                                <tr>
                                    <td class="fw-semibold">{{ \Carbon\Carbon::parse($att->date)->format('d M, Y') }}</td>
                                    <td>
                                        @if($att->check_in)
                                            {{ \Carbon\Carbon::parse($att->check_in)->format('h:i A') }}
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($att->check_out)
                                            {{ \Carbon\Carbon::parse($att->check_out)->format('h:i A') }}
                                        @else
                                            <span class="text-muted">--</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ number_format($att->total_hours, 2) }} hrs</span>
                                    </td>
                                    <td>
                                        @if($att->status === 'present')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Present</span>
                                        @elseif($att->status === 'half_day')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Half Day</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Absent</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $att->notes ?? '-' }}</small>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No attendance records found for this employee.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($attendances->hasPages())
                <div class="card-footer bg-white">
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let isPasswordVisible = false;
        const passwordText = $('#passwordText');
        const eyeIcon = $('#passwordEyeIcon');
        const actualPassword = passwordText.data('password');

        $('#togglePasswordVisibilityBtn').on('click', function() {
            if (!isPasswordVisible) {
                passwordText.text(actualPassword);
                eyeIcon.removeClass('fa-eye').addClass('fa-eye-slash');
                isPasswordVisible = true;
            } else {
                passwordText.text('••••••••');
                eyeIcon.removeClass('fa-eye-slash').addClass('fa-eye');
                isPasswordVisible = false;
            }
        });

        $('#copyPasswordBtn').on('click', function() {
            navigator.clipboard.writeText(actualPassword).then(function() {
                Toast.fire({
                    icon: 'success',
                    title: 'Password copied to clipboard!'
                });
            }).catch(function() {
                // Fallback for older browsers
                const tempInput = $('<input>');
                $('body').append(tempInput);
                tempInput.val(actualPassword).select();
                document.execCommand('copy');
                tempInput.remove();
                Toast.fire({
                    icon: 'success',
                    title: 'Password copied to clipboard!'
                });
            });
        });
    });
</script>
@endsection
