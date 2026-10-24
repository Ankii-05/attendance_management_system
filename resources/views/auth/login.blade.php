@extends('layouts.app')

@section('title', 'Sign In - AMS')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="col-md-5 col-lg-4">
        <!-- Clean, minimal login card -->
        <div class="card shadow-sm border p-3">
            <div class="card-body p-3">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center bg-light rounded-circle border mb-3" style="width: 52px; height: 52px;">
                        <i class="fas fa-fingerprint text-primary fs-5"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Login</h4>
                    <p class="text-muted small mb-0">Sign in to your account</p>
                </div>

                <!-- Form -->
                <form id="loginForm" method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <!-- Email Input -->
                    <div class="mb-3">
                        <label for="email" class="form-label small fw-semibold text-secondary">Email address</label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="name@company.com" 
                               required 
                               autofocus>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password Input with Eye Icon -->
                    <div class="mb-3">
                        <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                        <div class="input-group">
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" style="border-color: #cbd5e1;" title="Show or hide password">
                                <i class="far fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Remember me -->
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="remember">Keep me logged in</label>
                        </div>
                    </div>

                    <!-- Submit button -->
                    <div class="d-grid mb-2">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold" id="submitBtn">
                            Sign In
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- Office Hours Note -->
        <div class="text-center mt-4">
            <div class="d-inline-flex align-items-center gap-2 bg-white border shadow-sm px-3 py-2 rounded-pill small fw-medium text-dark mb-4">
                <i class="far fa-clock text-primary"></i>
                <span>Official Work Duration: <strong>8 Hours</strong></span>
            </div>
            <!-- <p class="text-muted small mt-2 mb-1" style="font-size: 0.78rem;">
                &lt; 4 hrs: <strong>Absent</strong> &bull; 4 to 8 hrs: <strong>Half Day</strong> &bull; &ge; 8 hrs: <strong>Present</strong>
            </p> -->
            <p class="text-muted small mb-0" style="font-size: 0.74rem;">
                AMS &copy; {{ date('Y') }} Attendance Management System
            </p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Toggle password visibility
        $('#togglePasswordBtn').on('click', function() {
            const passwordInput = $('#password');
            const icon = $('#togglePasswordIcon');
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                passwordInput.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });

        // jQuery client-side form validation
        $('#loginForm').validate({
            rules: {
                email: {
                    required: true,
                    email: true
                },
                password: {
                    required: true,
                    minlength: 6
                }
            },
            messages: {
                email: {
                    required: "Please enter your email address",
                    email: "Please enter a valid email format"
                },
                password: {
                    required: "Please enter your password",
                    minlength: "Password must be at least 6 characters"
                }
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
