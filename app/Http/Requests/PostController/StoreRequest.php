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
            'content' => 'required|string|max:280',

            'images' => 'nullable|array|max:4',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // Max 10MB per image

            'videos' => 'nullable|array|max:4',
            'videos.*' => 'nullable|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-ms-wmv|max:51200', // Max 50MB per video

            // JSON string: [{ type: 'image'|'video', index: number }, ...]
            'media_order' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Konten post tidak boleh kosong.',
            'content.string' => 'Konten post harus berupa teks.',
            'content.max' => 'Konten post tidak boleh lebih dari :max karakter.',
            'images.*.image' => 'Setiap file harus berupa gambar.',
            'images.*.mimes' => 'Gambar harus berjenis: jpeg, png, jpg, gif, webp.',
            'images.*.max' => 'Setiap gambar tidak boleh lebih dari :max kilobyte.',
            'images.array' => 'Gambar harus berupa array.',
            'images.max' => 'Anda tidak boleh mengunggah lebih dari :max gambar.',

            'videos.*.mimetypes' => 'Video harus berjenis: mp4, mov, avi, wmv.',
            'videos.*.max' => 'Setiap video tidak boleh lebih dari :max kilobyte.',
            'videos.array' => 'Video harus berupa array.',
            'videos.max' => 'Anda tidak boleh mengunggah lebih dari :max video.',
        ];
    }

    // request
}
