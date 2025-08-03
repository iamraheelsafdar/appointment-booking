<?php

namespace App\Http\Requests;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class BaseRequestApi extends FormRequest
{
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors()->all();
        // Redirect back with the errors
        throw new HttpResponseException(response()->json(['errors' => $errors], 400));
    }
}
