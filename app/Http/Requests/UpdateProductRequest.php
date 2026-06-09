<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'uuid', 'exists:categories,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'feature' => ['nullable', 'string'],
            'area' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'integer'],
            'thumbnail_id' => ['nullable', 'uuid', 'exists:attachments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.uuid' => 'Category must be a valid ID.',
            'category_id.exists' => 'Category not found.',
            'title.required' => 'Title is required.',
            'title.string' => 'Title must be a valid text.',
            'title.max' => 'Title must not be greater than 255 characters.',
            'description.string' => 'Description must be a valid text.',
            'feature.string' => 'Feature must be a valid text.',
            'area.string' => 'Area must be a valid text.',
            'area.max' => 'Area must not be greater than 255 characters.',
            'thumbnail_id.uuid' => 'Thumbnail must be a valid attachment ID.',
            'thumbnail_id.exists' => 'Thumbnail attachment not found.',
        ];
    }
}
