<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AMS - Attendance Management System')</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Select2 CSS & Select2 Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    
    <!-- Custom External CSS -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    @yield('styles')
</head>
<body>
    <div id="app">
        <!-- Minimal Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-custom sticky-top">
            <div class="container-fluid px-lg-4">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <span class="brand-dot"></span>
                    <span>Attendance Management System</span>
                </a>



                <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#topNav">
                    <i class="fas fa-bars text-secondary"></i>
                </button>

                <div class="collapse navbar-collapse" id="topNav">
                    @auth
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                            @if(auth()->user()->isAdmin())
                                <!-- Admin Links -->
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                        <i class="fas fa-grid-2 me-1"></i> Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}" href="{{ route('admin.employees.index') }}">
                                        <i class="fas fa-users me-1"></i> Employees
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.attendance.index') ? 'active' : '' }}" href="{{ route('admin.attendance.index') }}">
                                        <i class="fas fa-calendar-check me-1"></i> Attendance
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.attendance.absent') ? 'active' : '' }}" href="{{ route('admin.attendance.absent') }}">
                                        <i class="fas fa-user-xmark me-1"></i> Today's Absentees
                                    </a>
                                </li>
                            @else
                                <!-- Employee Links -->
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}" href="{{ route('employee.dashboard') }}">
                                        <i class="fas fa-house me-1"></i> My Dashboard
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('employee.attendance.*') ? 'active' : '' }}" href="{{ route('employee.attendance.index') }}">
                                        <i class="fas fa-clock-rotate-left me-1"></i> Attendance History
                                    </a>
                                </li>
                            @endif
                        </ul>

                        <!-- User Profile and Logout -->
                        <ul class="navbar-nav ms-auto align-items-center">
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-dark d-flex align-items-center gap-2" href="#" id="userMenu" role="button" data-bs-toggle="dropdown">
                                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center text-secondary fw-semibold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                    <span>{{ auth()->user()->name }}</span>
                                    <span class="badge {{ auth()->user()->isAdmin() ? 'badge-soft-danger' : 'badge-soft-primary' }} rounded-pill px-2">
                                        {{ ucfirst(auth()->user()->role) }}
                                    </span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2">
                                    <li><h6 class="dropdown-header text-muted">{{ auth()->user()->email }}</h6></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}" id="logoutForm">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                                <i class="fas fa-arrow-right-from-bracket"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    @else
                        <!-- Guest Navigation -->
                        <!-- <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link active" href="{{ route('login') }}">
                                    <i class="fas fa-arrow-right-to-bracket me-1"></i> Sign In
                                </a>
                            </li>
                        </ul> -->
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Main Body Content -->
        <main class="py-4">
            <div class="container-fluid px-lg-4">
                @yield('content')
            </div>
        </main>
    </div>



    <!-- Core Scripts: jQuery, Bootstrap, jQuery Validation, DataTables, SweetAlert2, Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Global Setup: CSRF, SweetAlert Notifications, Select2, and Confirmation Dialogs -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Initialize Select2 on all dropdowns
        $(document).ready(function() {
            $('select.form-select:not(.no-select2), select.select2').each(function() {
                if ($(this).parents('.dataTables_length').length) {
                    return;
                }
                const placeholder = $(this).find('option[value=""]').first().text() || $(this).data('placeholder') || 'Select an option';
                $(this).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: placeholder,
                    allowClear: $(this).find('option[value=""]').length > 0
                });
            });

            // Trigger jQuery validate re-check on Select2 change
            $(document).on('change.select2', 'select', function() {
                if ($(this).closest('form').data('validator')) {
                    $(this).valid();
                }
            });
        });

        // Global SweetAlert Toast Mixin
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        // Handle Laravel Flash Messages via SweetAlert
        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ addslashes(session('success')) }}"
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Attention',
                text: "{{ addslashes(session('error')) }}",
                confirmButtonColor: '#2563eb'
            });
        @endif

        @if($errors->has('general'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ addslashes($errors->first('general')) }}",
                confirmButtonColor: '#2563eb'
            });
        @endif

        // Global SweetAlert confirmation handler for forms with data-confirm
        $(document).on('submit', 'form[data-confirm]', function(e) {
            e.preventDefault();
            const form = this;
            const message = $(form).data('confirm') || 'Are you sure you want to proceed?';
            const confirmBtnText = $(form).data('confirm-btn') || 'Yes, Proceed';
            const confirmBtnClass = $(form).data('confirm-color') || '#2563eb';
            const icon = $(form).data('confirm-icon') || 'warning';

            Swal.fire({
                title: 'Please Confirm',
                text: message,
                icon: icon,
                showCancelButton: true,
                confirmButtonColor: confirmBtnClass,
                cancelButtonColor: '#64748b',
                confirmButtonText: confirmBtnText,
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Temporarily remove attribute so it submits normally
                    $(form).removeAttr('data-confirm');
                    form.submit();
                }
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
