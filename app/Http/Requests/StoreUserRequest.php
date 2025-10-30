<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'office_id' => 'required|exists:offices,id',
            'role' => ['required', Rule::in(['karyawan', 'supervisor', 'admin'])],
            'work_time_type' => ['required', Rule::in(['tetap', 'bebas'])],
            'ontime_incentive' => 'nullable|numeric|min:0',
            'out_of_town_incentive' => 'nullable|numeric|min:0',
            'holiday_incentive' => 'nullable|numeric|min:0',
            'overtime_rate_per_hour' => 'nullable|numeric|min:0',
            'annual_leave_quota' => 'nullable|integer|min:0|max:365',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama harus diisi',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah terdaftar',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 6 karakter',
            'office_id.required' => 'Kantor harus dipilih',
            'office_id.exists' => 'Kantor tidak valid',
            'role.required' => 'Role harus dipilih',
            'work_time_type.required' => 'Tipe jam kerja harus dipilih',
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
