<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LeaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['cuti', 'sakit', 'izin'])],
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|string', // Base64 encoded file for medical certificate
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Tipe cuti/izin harus diisi',
            'type.in' => 'Tipe harus cuti, sakit, atau izin',
            'start_date.required' => 'Tanggal mulai harus diisi',
            'start_date.after_or_equal' => 'Tanggal mulai minimal hari ini',
            'end_date.required' => 'Tanggal selesai harus diisi',
            'end_date.after_or_equal' => 'Tanggal selesai harus >= tanggal mulai',
            'reason.required' => 'Alasan harus diisi',
            'reason.max' => 'Alasan maksimal 1000 karakter',
        ];
    }
}
