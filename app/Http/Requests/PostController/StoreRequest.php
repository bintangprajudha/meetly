<?php

namespace App\Http\Requests\PostController;

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
        return  [
            'content' => 'required|string|min:5|max:280',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Multiple images
            'images' => 'nullable|array|max:4', // Max 4 images
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Konten post tidak boleh kosong.',
            'content.string' => 'Konten post harus berupa teks.',
            'content.min' => 'Konten post minimal harus :min karakter.',
            'content.max' => 'Konten post tidak boleh lebih dari :max karakter.',
            'images.*.image' => 'Setiap file harus berupa gambar.',
            'images.*.mimes' => 'Gambar harus berjenis: jpeg, png, jpg, gif, webp.',
            'images.*.max' => 'Setiap gambar tidak boleh lebih dari :max kilobyte.',
            'images.array' => 'Gambar harus berupa array.',
            'images.max' => 'Anda tidak boleh mengunggah lebih dari :max gambar.',
        ];
    }

    // request
}
