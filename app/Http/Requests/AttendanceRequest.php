<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\AttendanceStatus;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $rules = [
            'employee_id' => ['required', 'exists:users,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'in:' . implode(',', [
                AttendanceStatus::PRESENT,
                AttendanceStatus::ABSENT,
                AttendanceStatus::LATE,
                AttendanceStatus::HALF_DAY,
                AttendanceStatus::LEAVE
            ])],
            'notes' => ['nullable', 'string', 'max:500']
        ];

        // Check-in and check-out are required for present status
        if ($this->status === AttendanceStatus::PRESENT) {
            $rules['check_in'] = ['required', 'date_format:H:i:s'];
            $rules['check_out'] = ['nullable', 'date_format:H:i:s', 'after:check_in'];
        }

        // For absent and leave, check-in and check-out should be null
        if (in_array($this->status, [AttendanceStatus::ABSENT, AttendanceStatus::LEAVE])) {
            $rules['check_in'] = ['nullable'];
            $rules['check_out'] = ['nullable'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Employee is required',
            'employee_id.exists' => 'Selected employee does not exist',
            'date.required' => 'Date is required',
            'date.date' => 'Date must be a valid date',
            'date.before_or_equal' => 'Date cannot be in the future',
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status selected',
            'check_in.required' => 'Check-in time is required for present status',
            'check_in.date_format' => 'Check-in time must be in HH:MM:SS format',
            'check_out.date_format' => 'Check-out time must be in HH:MM:SS format',
            'check_out.after' => 'Check-out time must be after check-in time',
            'notes.max' => 'Notes cannot exceed 500 characters'
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional business logic validation
            if ($this->status === AttendanceStatus::PRESENT && $this->check_in && $this->check_out) {
                $checkIn = \Carbon\Carbon::parse($this->check_in);
                $checkOut = \Carbon\Carbon::parse($this->check_out);
                
                if ($checkIn->diffInHours($checkOut) > 24) {
                    $validator->errors()->add('check_out', 'Working hours cannot exceed 24 hours');
                }
            }
        });
    }
}
