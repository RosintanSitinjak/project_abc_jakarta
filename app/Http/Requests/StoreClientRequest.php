<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
            'position' => ['nullable', 'integer'],
            'logo_id' => ['nullable', 'uuid', 'exists:attachments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Client name is required.',
            'name.string' => 'Client name must be a valid text.',
            'name.max' => 'Client name must not be greater than 255 characters.',
            'logo_id.uuid' => 'Logo must be a valid attachment ID.',
            'logo_id.exists' => 'Logo attachment not found.',
        ];
    }
}
