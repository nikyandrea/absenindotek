<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckInRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'required|numeric|min:0|max:100',
            'face_image' => 'required|string', // Base64 encoded image
            'is_mock_location' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'Latitude harus diisi',
            'latitude.between' => 'Latitude tidak valid',
            'longitude.required' => 'Longitude harus diisi',
            'longitude.between' => 'Longitude tidak valid',
            'accuracy.required' => 'Akurasi GPS harus diisi',
            'accuracy.max' => 'Akurasi GPS terlalu rendah (maksimal 100m)',
            'face_image.required' => 'Foto wajah harus diisi',
            'is_mock_location.required' => 'Status mock location harus diisi',
        ];
    }
}
