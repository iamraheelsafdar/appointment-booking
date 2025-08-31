<?php

namespace App\Http\Requests\Availability;

use App\Http\Requests\BaseRequest;
use App\Http\Requests\BaseRequestApi;
use Illuminate\Contracts\Validation\ValidationRule;

class CreateAvailabilityRequest extends BaseRequestApi
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
            'availabilities' => 'required|array',
            'availabilities.*.day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'availabilities.*.start_time' => 'required|date_format:H:i',
            'availabilities.*.end_time' => 'required|date_format:H:i|after:availabilities.*.start_time',
        ];
    }
}
