@extends('layouts.app')

@section('title', 'Employees - AMS')

@section('content')
<!-- Page Header -->
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h4 class="fw-bold text-dark mb-1">Employee Management</h4>
        <p class="text-muted small mb-0">Search, filter by department or status, and view employee account passwords.</p>
    </div>
    <div>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Employee
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="row g-2 align-items-center">
            <!-- Filter by Department -->
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Department</label>
                <select id="filter_department" class="form-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filter by Status -->
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Account Status</label>
                <select id="filter_status" class="form-select">
                    <option value="">All Statuses (Active & Inactive)</option>
                    <option value="active">Active Employees</option>
                    <option value="inactive">Inactive Employees</option>
                </select>
            </div>

            <!-- Reset Button -->
            <div class="col-md-2 d-flex align-items-end">
                <div class="w-100">
                    <label class="form-label small text-muted mb-1 d-none d-md-block">&nbsp;</label>
                    <button type="button" id="btnResetFilters" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-rotate-left me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Employees DataTable Card -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom fw-semibold text-dark d-flex justify-content-between align-items-center">
        <span><i class="fas fa-users text-primary me-2"></i>Staff Directory</span>
        <span class="text-muted small">Live AJAX Search & Filtering</span>
    </div>
    <div class="card-body p-3">
        <div class="table-responsive">
            <table class="table align-middle w-100" id="employeesTable">
                <thead>
                    <tr>
                        <th style="width: 40px;">S.No   </th>
                        <th>Code</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Password</th>
                        <th>Mobile</th>
                        <th>Department</th>
                        <th>Joining Date</th>
                        <th>Status</th>
                        <th style="min-width: 165px; width: 165px;" class="text-center">Actions</th>
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
        var table = $('#employeesTable').DataTable({
            // processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.employees.index') }}",
                data: function(d) {
                    d.department_id = $('#filter_department').val();
                    d.status = $('#filter_status').val();
                }
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'employee_code', name: 'employee_code' },
                { data: 'name', name: 'name', className: 'fw-semibold text-dark' },
                { data: 'email', name: 'email', className: 'text-muted' },
                { data: 'password_display', name: 'initial_password', orderable: false, searchable: false },
                { data: 'mobile_number', name: 'mobile_number', className: 'text-muted' },
                { data: 'department_name', name: 'department.name' },
                { data: 'joining_date_formatted', name: 'joining_date' },
                { data: 'status_badge', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search staff in real-time...",
                processing: '<div class="spinner-border spinner-border-sm text-primary" role="status"></div>'
            },
            drawCallback: function() {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });

        // Trigger instant AJAX redraw without page reload on filter change
        $('#filter_department, #filter_status').on('change', function() {
            table.draw();
        });

        // Reset filters with instant AJAX table refresh
        $('#btnResetFilters').on('click', function() {
            $('#filter_department').val('').trigger('change');
            $('#filter_status').val('').trigger('change');
            table.search('').draw();
        });

        // Toggle table password masked/unmasked
        $(document).on('click', '.toggle-tbl-pwd', function(e) {
            e.preventDefault();
            const container = $(this).closest('.d-inline-flex');
            const span = container.find('.tbl-password-val');
            const icon = $(this).find('i');
            const actualPassword = span.data('pwd');

            if (span.text() === '••••••••') {
                span.text(actualPassword);
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                span.text('••••••••');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });
    });
</script>
@endsection
