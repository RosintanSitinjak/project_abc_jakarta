<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteSettingRequest extends FormRequest
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
            'about_us' => ['nullable', 'string'],
            'vision' => ['nullable', 'string'],
            'mission' => ['nullable', 'string'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email'],
            'site_name' => ['nullable', 'string', 'max:255'],
            'linkedin_url' => ['nullable', 'url'],
            'facebook_url' => ['nullable', 'url'],
            'twitter_url' => ['nullable', 'url'],
            'instagram_url' => ['nullable', 'url'],
            'site_description' => ['nullable', 'string'],
            'logo_id' => ['nullable', 'uuid', 'exists:attachments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'about_us.string' => 'About us must be a valid text.',
            'vision.string' => 'Vision must be a valid text.',
            'mission.string' => 'Mission must be a valid text.',
            'whatsapp_number.string' => 'WhatsApp number must be a valid text.',
            'whatsapp_number.max' => 'WhatsApp number must not be greater than 30 characters.',
            'address.string' => 'Address must be a valid text.',
            'phone.string' => 'Phone must be a valid text.',
            'phone.max' => 'Phone must not be greater than 30 characters.',
            'email.email' => 'Email must be a valid email address.',
            'site_name.string' => 'Site name must be a valid text.',
            'site_name.max' => 'Site name must not be greater than 255 characters.',
            'linkedin_url.url' => 'LinkedIn URL must be a valid URL.',
            'facebook_url.url' => 'Facebook URL must be a valid URL.',
            'twitter_url.url' => 'Twitter URL must be a valid URL.',
            'instagram_url.url' => 'Instagram URL must be a valid URL.',
            'site_description.string' => 'Site description must be a valid text.',
            'logo_id.uuid' => 'Logo must be a valid attachment ID.',
            'logo_id.exists' => 'Logo attachment not found.',
        ];
    }
}
