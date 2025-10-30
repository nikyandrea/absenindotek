<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LateReasonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => 'required|string|max:500',
            'improvement_plan' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Alasan keterlambatan harus diisi',
            'reason.max' => 'Alasan maksimal 500 karakter',
            'improvement_plan.required' => 'Rencana perbaikan harus diisi',
            'improvement_plan.max' => 'Rencana perbaikan maksimal 500 karakter',
        ];
    }
}
