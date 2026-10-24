<?php

namespace App\Http\Requests\Attendance;

use App\Helpers\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckInRequest extends FormRequest
{
    // Authorize attendance check-in
    public function authorize(): bool
    {
        return true;
    }

    // Validation rules for check-in
    public function rules(): array
    {
        return [
            'employee_id' => ['nullable', 'exists:employees,id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    // Custom messages
    public function messages(): array
    {
        return [
            'employee_id.exists' => 'Selected employee does not exist.',
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
