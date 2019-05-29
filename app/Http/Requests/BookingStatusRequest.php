<?php

namespace App\Http\Requests;

use App\Utilities\DBStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'status'     => [
                'required',
                Rule::in(
                    [
                        DBStatus::BOOKING_ACCEPTED,
                        DBStatus::BOOKING_CANCELLED,
                        DBStatus::BOOKING_REJECTED,
                        DBStatus::BOOKING_POST_REJECTED,
                    ]
                )
            ],
            'booking_id' => [
                'required',
                'integer',
                'exists:bookings,id'
            ]
        ];
    }
}
