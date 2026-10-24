@extends('layouts.app')

@section('title', 'Edit Employee - AMS')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <!-- Header & Back Button -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-0 text-gray-800">Edit Employee: {{ $employee->name }}</h3>
                <p class="text-muted small mb-0">Update employee profile, department, contact information, or status.</p>
            </div>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to List
            </a>
        </div>

        <!-- Form Card -->
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <i class="fas fa-user-edit text-primary me-2"></i>Update Employee Details
            </div>
            <div class="card-body p-4">
                <form id="editEmployeeForm" method="POST" action="{{ route('admin.employees.update', $employee->id) }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <!-- Employee Code -->
                        <div class="col-md-6">
                            <label for="employee_code" class="form-label fw-semibold">
                                Employee Code 
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-id-badge"></i></span>
                                <input type="text" 
                                       class="form-control bg-light" 
                                       id="employee_code" 
                                       name="employee_code" 
                                       value="{{ old('employee_code', $employee->employee_code) }}" 
                                       readonly 
                                       style="cursor: not-allowed; font-weight: 600;">
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $employee->name) }}" 
                                   required>
                            @error('name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $employee->email) }}" 
                                   required>
                            @error('email')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mobile Number -->
                        <div class="col-md-6">
                            <label for="mobile_number" class="form-label fw-semibold">Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('mobile_number') is-invalid @enderror" 
                                   id="mobile_number" 
                                   name="mobile_number" 
                                   value="{{ old('mobile_number', $employee->mobile_number) }}" 
                                   placeholder="e.g. 9876543210 (10 digits starting 6-9)"
                                   maxlength="10"
                                   required>
                            @error('mobile_number')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Department -->
                        <div class="col-md-6">
                            <label for="department_id" class="form-label fw-semibold">Department <span class="text-danger">*</span></label>
                            <select class="form-select @error('department_id') is-invalid @enderror" id="department_id" name="department_id" required>
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Designation -->
                        <div class="col-md-6">
                            <label for="designation" class="form-label fw-semibold">Designation <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control @error('designation') is-invalid @enderror" 
                                   id="designation" 
                                   name="designation" 
                                   value="{{ old('designation', $employee->designation) }}" 
                                   required>
                            @error('designation')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Joining Date -->
                        <div class="col-md-6">
                            <label for="joining_date" class="form-label fw-semibold">Joining Date <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control @error('joining_date') is-invalid @enderror" 
                                   id="joining_date" 
                                   name="joining_date" 
                                   value="{{ old('joining_date', $employee->joining_date?->format('Y-m-d')) }}" 
                                   required>
                            @error('joining_date')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $employee->status) === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Change Password (Optional) with Eye Toggle -->
                        <div class="col-12">
                            <label for="password" class="form-label fw-semibold">Reset Password (Optional)</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Leave blank to keep existing password">
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" title="Show or hide password">
                                    <i class="far fa-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <small class="text-muted">Current stored password: <strong>{{ $employee->initial_password ?? 'Password@123' }}</strong></small>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-light border px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Update Employee
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Custom validator for 10-digit mobile number starting with 6, 7, 8, or 9
        $.validator.addMethod("indianMobile", function(value, element) {
            return this.optional(element) || /^[6-9]\d{9}$/.test(value);
        }, "Mobile number must be exactly 10 digits starting with 6, 7, 8, or 9");

        // Toggle password visibility
        $('#togglePasswordBtn').on('click', function() {
            const input = $('#password');
            const icon = $('#togglePasswordIcon');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // jQuery validation for editing employee
        $('#editEmployeeForm').validate({
            rules: {
                name: {
                    required: true,
                    maxlength: 150
                },
                email: {
                    required: true,
                    email: true,
                    maxlength: 150
                },
                mobile_number: {
                    required: true,
                    digits: true,
                    indianMobile: true
                },
                department_id: {
                    required: true
                },
                designation: {
                    required: true,
                    maxlength: 100
                },
                joining_date: {
                    required: true,
                    date: true
                },
                status: {
                    required: true
                },
                password: {
                    minlength: 6
                }
            },
            messages: {
                employee_code: "Please enter an employee code",
                name: "Please enter employee full name",
                email: "Please enter a valid email address",
                mobile_number: {
                    required: "Please enter a mobile number",
                    digits: "Mobile number must contain digits only",
                    indianMobile: "Mobile number must be exactly 10 digits starting with 6, 7, 8, or 9"
                },
                department_id: "Please select a department",
                designation: "Please enter the designation",
                joining_date: "Please select a valid joining date",
                status: "Please select employee status",
                password: "Password must be at least 6 characters"
            },
            errorElement: 'label',
            errorPlacement: function (error, element) {
                error.addClass('error');
                if (element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function (element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element) {
                $(element).removeClass('is-invalid');
            }
        });
    });
</script>
@endsection
