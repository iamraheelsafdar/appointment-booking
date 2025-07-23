<?php

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        // Flash the error messages to the session
        session()->flash('errors', $errors);
        // Redirect back with the errors
        throw new HttpResponseException(redirect()->back()->withInput());
    }
}
