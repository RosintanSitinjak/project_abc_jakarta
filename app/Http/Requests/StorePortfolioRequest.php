<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePortfolioRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'areas' => ['nullable', 'array'],
            'areas.*' => ['string', 'max:255'],
            'thumbnail_id' => ['nullable', 'uuid', 'exists:attachments,id'],
            'photo_ids' => ['nullable', 'array'],
            'photo_ids.*' => ['uuid', 'exists:attachments,id'],
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
            'client_name.string' => 'Client name must be a valid text.',
            'client_name.max' => 'Client name must not be greater than 255 characters.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be greater than or equal to 1900.',
            'year.max' => 'Year must be less than or equal to 2100.',
            'areas.array' => 'Areas must be an array.',
            'areas.*.string' => 'Each area must be a valid text.',
            'areas.*.max' => 'Each area must not be greater than 255 characters.',
            'thumbnail_id.uuid' => 'Thumbnail must be a valid attachment ID.',
            'thumbnail_id.exists' => 'Thumbnail attachment not found.',
            'photo_ids.array' => 'Photos must be an array.',
            'photo_ids.*.uuid' => 'Each photo must be a valid attachment ID.',
            'photo_ids.*.exists' => 'Photo attachment not found.',
        ];
    }
}
