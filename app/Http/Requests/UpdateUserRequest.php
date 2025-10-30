<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Laravel apiResource uses 'user' as default parameter name
        $userId = $this->route('user') ?? $this->route('id');

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => 'nullable|string|min:6',
            'phone' => 'nullable|string|max:20',
            'office_id' => 'sometimes|required|exists:offices,id',
            'role' => ['sometimes', 'required', Rule::in(['karyawan', 'supervisor', 'admin'])],
            'work_time_type' => ['sometimes', 'required', Rule::in(['tetap', 'bebas'])],
            
            // Accept both shorthand and full column names
            'ontime_incentive' => 'nullable|numeric|min:0',
            'out_of_town_incentive' => 'nullable|numeric|min:0',
            'holiday_incentive' => 'nullable|numeric|min:0',
            'ontime_incentive_per_day' => 'nullable|numeric|min:0',
            'out_of_town_incentive_per_day' => 'nullable|numeric|min:0',
            'holiday_incentive_per_day' => 'nullable|numeric|min:0',
            'overtime_rate_per_hour' => 'nullable|numeric|min:0',
            'annual_leave_quota' => 'nullable|integer|min:0|max:365',
            
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah digunakan user lain',
            'password.min' => 'Password minimal 6 karakter',
            'office_id.exists' => 'Kantor tidak valid',
        ];
    }

    /**
     * Prepare data for validation - map shorthand names to database column names
     */
    protected function prepareForValidation(): void
    {
        $data = [];
        
        // Map shorthand incentive names to actual database columns
        if ($this->has('ontime_incentive')) {
            $data['ontime_incentive_per_day'] = $this->input('ontime_incentive');
        }
        if ($this->has('out_of_town_incentive')) {
            $data['out_of_town_incentive_per_day'] = $this->input('out_of_town_incentive');
        }
        if ($this->has('holiday_incentive')) {
            $data['holiday_incentive_per_day'] = $this->input('holiday_incentive');
        }
        
        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
