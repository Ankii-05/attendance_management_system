@extends('layouts.app')

@section('title', 'Attendance Records - AMS')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1">Attendance Log</h4>
        <p class="text-muted small mb-0">Daily employee attendance entries, work durations, and status.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.attendance.absent') }}" class="btn btn-outline-danger">
            <i class="fas fa-user-xmark me-1"></i> Today's Absentees ({{ $metrics['today_absent'] }})
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row g-2 align-items-end">
            <!-- Filter by Employee -->
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Employee</label>
                <select id="filter_employee" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter by Department -->
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Department</label>
                <select id="filter_department" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter by Date -->
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Date</label>
                <input type="date" id="filter_date" class="form-control" placeholder="Select Date">
            </div>

            <!-- Filter by Status -->
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select id="filter_status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="present">Present</option>
                    <option value="half_day">Half Day</option>
                    <option value="absent">Absent</option>
                </select>
            </div>

            <!-- Reset Button -->
            <div class="col-md-1">
                <button type="button" id="btnResetFilters" class="btn btn-outline-secondary w-100" title="Reset Filters">
                    <i class="fas fa-rotate-left"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Attendance DataTable Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom fw-semibold text-dark d-flex justify-content-between align-items-center">
        <span><i class="fas fa-calendar-days text-primary me-2"></i>Attendance Entries</span>
        <span class="text-muted small">Live AJAX Search & Filtering</span>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table align-middle w-100" id="attendanceTable">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Code</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Populated via Yajra DataTables AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Yajra DataTable with AJAX (no page reloads)
        var table = $('#attendanceTable').DataTable({
            // processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.attendance.index') }}",
                data: function(d) {
                    d.employee_id = $('#filter_employee').val();
                    d.department_id = $('#filter_department').val();
                    d.date = $('#filter_date').val();
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'employee_code', name: 'employee.employee_code' },
                { data: 'employee_name', name: 'employee.name', className: 'fw-semibold text-dark' },
                { data: 'department', name: 'employee.department.name' },
                { data: 'date_formatted', name: 'date' },
                { data: 'check_in_formatted', name: 'check_in' },
                { data: 'check_out_formatted', name: 'check_out' },
                { data: 'hours_badge', name: 'total_hours' },
                { data: 'status_badge', name: 'status' }
            ],
            order: [[4, 'desc'], [0, 'desc']],
            pageLength: 15,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search attendance in real-time...",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>'
            }
        });

        // Event listeners for filters (AJAX redraw without page reload)
        $('#filter_employee, #filter_department, #filter_date, #filter_status').on('change', function() {
            table.draw();
        });

        // Reset filter inputs and search
        $('#btnResetFilters').on('click', function() {
            $('#filter_employee').val('').trigger('change');
            $('#filter_department').val('').trigger('change');
            $('#filter_date').val('');
            $('#filter_status').val('').trigger('change');
            table.search('').draw();
        });
    });
</script>
@endsection
