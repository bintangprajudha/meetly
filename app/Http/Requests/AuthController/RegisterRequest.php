<?php

namespace App\Http\Requests\AuthController;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    // public function messages(): array
    // {
    //     return [
    //         'name.required' => 'maksimal 255 karakter.',
    //         'name.string' => 'Nama harus berupa teks.', 
    //         'email.required' => 'Email tidak boleh kosong.',
    //         'email.string' => 'Email harus berupa teks.',
    //         'email.email' => 'Format email tidak valid.',
    //         'email.max' => 'Email maksimal 255 karakter.',
    //         'email.unique' => 'Email sudah terdaftar.',
    //         'password.required' => 'Password tidak boleh kosong.',
    //         'password.string' => 'Password harus berupa teks.',
    //         'password.min' => 'Password minimal 8 karakter.',
    //         'password.confirmed' => 'Konfirmasi password tidak sesuai.',
    //     ];
    // }

    // request
}
