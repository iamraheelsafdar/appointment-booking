<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends BaseRequest
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
            'email' => 'required|email|unique:users,email',
            'name' => 'required|min:3|max:255',
            'coach_type' => ['required' , Rule::in(['High Level Coach', 'Normal Coach'])],
            'phone' => ['required', 'regex:/^[0-9\s\-\+\(\)]{10,20}$/'],
        ];
    }
}
