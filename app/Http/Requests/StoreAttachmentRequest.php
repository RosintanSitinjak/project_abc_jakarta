<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
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
            'user_id' => ['nullable', 'uuid', 'exists:users,id'],
            'attachmentable_id' => ['required', 'uuid'],
            'attachmentable_type' => ['required', 'string'],
            'file' => ['nullable', 'file', 'max:5120'],
            'name' => ['required_without:file', 'string', 'max:255'],
            'path' => ['required_without:file', 'string', 'max:2048'],
            'size' => ['nullable', 'integer', 'min:0'],
            'mime' => ['nullable', 'string', 'max:100'],
            'disk' => ['nullable', 'string', 'max:50'],
            'folder' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'remark' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.uuid' => 'User must be a valid ID.',
            'user_id.exists' => 'User not found.',
            'attachmentable_id.required' => 'Attachment target is required.',
            'attachmentable_id.uuid' => 'Attachment target must be a valid ID.',
            'attachmentable_type.required' => 'Attachment target type is required.',
            'attachmentable_type.string' => 'Attachment target type must be a valid text.',
            'file.file' => 'File must be a valid upload.',
            'file.max' => 'File size must not exceed 5MB.',
            'name.required' => 'File name is required.',
            'name.string' => 'File name must be a valid text.',
            'name.max' => 'File name must not be greater than 255 characters.',
            'path.required' => 'File path is required.',
            'path.string' => 'File path must be a valid text.',
            'path.max' => 'File path must not be greater than 2048 characters.',
            'size.integer' => 'File size must be a valid number.',
            'size.min' => 'File size must be zero or greater.',
            'mime.string' => 'Mime type must be a valid text.',
            'mime.max' => 'Mime type must not be greater than 100 characters.',
            'disk.string' => 'Disk must be a valid text.',
            'disk.max' => 'Disk must not be greater than 50 characters.',
            'folder.string' => 'Folder must be a valid text.',
            'folder.max' => 'Folder must not be greater than 255 characters.',
            'type.string' => 'Type must be a valid text.',
            'type.max' => 'Type must not be greater than 50 characters.',
            'remark.string' => 'Remark must be a valid text.',
        ];
    }
}
