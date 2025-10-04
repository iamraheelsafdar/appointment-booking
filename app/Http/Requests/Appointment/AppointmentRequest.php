<?php

namespace App\Http\Requests\Appointment;

use App\Http\Requests\BaseRequestApi;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Http\Requests\BaseRequest;

class AppointmentRequest extends BaseRequestApi
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
            'address' => 'required|max:150',
            'city' => 'required|max:150',
            'country' => 'required|max:150',
            'postalCode' => 'required|max:150',
            'state' => 'required|max:150',
            'suburb' => 'required|max:150',
            'email' => 'required|email|max:150',
            'phoneNumber' => 'required|string|max:20',
            'fullName' => 'required|max:150',
            'selectedDate' => 'required',
            'selectedTimeSlot' => 'required',
            'totalMinutes' => 'required',
            'lessons' => 'required|array',
            'playerType' => 'required|in:Returning,FreeTrial',
            'selectedCoach' => 'nullable|string',
            'selectedCoachId' => 'nullable|integer|exists:users,id',
        ];
    }
}
