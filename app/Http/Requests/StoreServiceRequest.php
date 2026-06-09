<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'thumbnail_id' => ['nullable', 'uuid', 'exists:attachments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Service name is required.',
            'name.string' => 'Service name must be a valid text.',
            'name.max' => 'Service name must not be greater than 255 characters.',
            'description.string' => 'Description must be a valid text.',
            'content.string' => 'Content must be a valid text.',
            'thumbnail_id.uuid' => 'Thumbnail must be a valid attachment ID.',
            'thumbnail_id.exists' => 'Thumbnail attachment not found.',
        ];
    }
}
