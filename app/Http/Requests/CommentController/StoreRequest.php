<?php

namespace App\Http\Requests\CommentController;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'content' => 'required|string|max:1000',
        ];
    }

    // public function messages(): array
    // {
    //     return [
    //         'content.required' => 'Komentar tidak boleh kosong.',
    //         'content.string' => 'Komentar harus berupa teks.',
    //         'content.max' => 'Komentar tidak boleh lebih dari :max karakter.',
    //     ];
    // }

    // request
}
