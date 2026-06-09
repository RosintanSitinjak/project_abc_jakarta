<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceScopeOfServiceRequest extends FormRequest
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
            'service_id' => ['sometimes', 'required', 'uuid', 'exists:services,id'],
            'scope' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Service is required.',
            'service_id.uuid' => 'Service must be a valid ID.',
            'service_id.exists' => 'Service not found.',
            'scope.required' => 'Scope is required.',
            'scope.string' => 'Scope must be a valid text.',
            'scope.max' => 'Scope must not be greater than 255 characters.',
        ];
    }
}
