<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DaftarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama'     => 'required|string|max:100',
            'password' => 'required|string|min:4',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'     => 'Nama wajib diisi.',
            'nama.string'       => 'Nama harus berupa teks.',
            'nama.max'          => 'Nama maksimal 100 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 4 karakter.',
        ];
    }
}
