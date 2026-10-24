<?php

namespace App\Http\Requests\Employee;

use App\Helpers\ApiResponse;
use App\Models\Employee;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEmployeeRequest extends FormRequest
{
    // Authorize request for admin
    public function authorize(): bool
    {
        return true;
    }

    // Validation rules for editing an employee
    public function rules(): array
    {
        $employeeParam = $this->route('employee') ?? $this->route('id');
        $employeeId = is_object($employeeParam) ? $employeeParam->id : $employeeParam;
        
        $userId = null;
        if ($employeeId) {
            $userId = Employee::where('id', $employeeId)->value('user_id');
        }

        return [
            'employee_code' => ['nullable', 'string', 'max:50', 'unique:employees,employee_code,' . $employeeId],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:employees,email,' . $employeeId, 'unique:users,email,' . ($userId ?: 'NULL')],
            'mobile_number' => ['required', 'string', 'regex:/^[6-9][0-9]{9}$/'],
            'department_id' => ['required', 'exists:departments,id'],
            'designation' => ['required', 'string', 'max:100'],
            'joining_date' => ['required', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'password' => ['nullable', 'string', 'min:6'],
        ];
    }

    // Custom inline error messages
    public function messages(): array
    {
        return [
            'employee_code.required' => 'Employee code is required.',
            'employee_code.unique' => 'This employee code is already taken.',
            'name.required' => 'Employee name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'mobile_number.required' => 'Mobile number is required.',
            'mobile_number.regex' => 'Mobile number must be a valid 10-digit number starting with 6, 7, 8, or 9.',
            'department_id.required' => 'Please select a department.',
            'department_id.exists' => 'Selected department does not exist.',
            'designation.required' => 'Designation is required.',
            'joining_date.required' => 'Joining date is required.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be active or inactive.',
        ];
    }

    // Format json validation errors for API requests
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(
                ApiResponse::error('Validation failed.', 422, $validator->errors())
            );
        }

        parent::failedValidation($validator);
    }
}
