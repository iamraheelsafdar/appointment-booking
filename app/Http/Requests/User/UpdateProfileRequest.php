<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateProfileRequest extends BaseRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        $valid = [
            'name' => 'required|string|max:255',
            'profile_image' => 'mimes:png,jpg,jpeg|max:2048',
        ];
        if (request()->new_password) {
            $valid['new_password'] = 'string|min:8|max:32';
            $valid['old_password'] = 'required|string|min:8|max:32';
        }
        return $valid;
    }

    public function messages(): array
    {
        return [
            'profile_image.mimes' => 'Profile image must be png, jpg, jpeg',
            'profile_image.max' => 'Profile image must not exceed 2 MB.',
        ];
    }
}
