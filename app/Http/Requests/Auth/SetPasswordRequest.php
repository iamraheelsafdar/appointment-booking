<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class SetPasswordRequest extends BaseRequest
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
            'email' => 'required|exists:users,email',
            'token' => 'required|exists:users,remember_token',
            'password' => 'required|string|min:8|max:32|confirmed',
            'password_confirmation' => 'required|string|min:8|max:32',
        ];
    }
}
