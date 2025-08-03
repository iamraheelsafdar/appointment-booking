<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateUserRequest extends BaseRequest
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
            'id' => 'required|exists:users,id',
            'name' => 'required|min:3|max:255',
            'phone' => ['required', 'regex:/^[0-9\s\-\+\(\)]{10,20}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'id.exists' => 'Requested user not exist'
        ];
    }
}
