<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

class UpdateProductRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'category' => ['sometimes', 'required', 'string', 'max:100'],
            'images' => ['sometimes', 'required', 'array', 'min:1'],
            'images.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value instanceof UploadedFile) {
                        if (! in_array(strtolower($value->getClientOriginalExtension()), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                            $fail("The {$attribute} must be an image file (jpg, jpeg, png, webp, gif).");
                        }
                    } elseif (! is_string($value)) {
                        $fail("The {$attribute} must be a valid image URL or uploaded image file.");
                    }
                },
            ],
        ];
    }
}
