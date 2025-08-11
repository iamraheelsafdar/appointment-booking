<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class SiteSettingRequest extends BaseRequest
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
        return [
            'logo' => 'mimes:png|max:2048',
            'title' => 'required|string|max:50',
            'copyright' => 'required|string|max:100',
            'buffer_minutes' => 'required|min:1|max:60',
            'slot_difference' => 'required|min:1|max:60',
        ];
    }

    public function messages(): array
    {
        return [
            'logo.mimes' => 'Logo must be in png format',
            'logo.max' => 'Logo must be less than 2MB',
        ];
    }
}
