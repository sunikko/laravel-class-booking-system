<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // The authenticated user must have an associated student profile to create a booking.
        return $this->user() && $this->user()->student;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'class_session_id' => ['required', 'integer', 'exists:class_sessions,id'],
            'booking_date' => ['required', 'date_format:Y-m-d'],
        ];
    }
}
