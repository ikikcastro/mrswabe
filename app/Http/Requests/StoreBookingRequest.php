<?php

namespace App\Http\Requests;

use App\Models\Room;
use App\Services\ReservationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates customer booking details using the app's date-only reservation fields.
 */
class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // The route requires authentication, so signed-in users can book.
        return true;
    }

    public function rules(): array
    {
        return [
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'guest_name' => ['required', 'string', 'max:255'],
            'stay_date' => ['required', 'date', 'after_or_equal:today'],
            'guest_count' => ['required', 'integer', 'min:1'],
            'duration_hours' => ['required', 'integer', Rule::in(ReservationService::STAY_OPTIONS)],
            'contact_number' => ['required', 'string', 'max:20'],
            'booking_type' => ['nullable', 'in:pay_now,reserve'],
            'payment_method' => ['required_unless:booking_type,reserve', 'nullable', 'in:gcash,maribank'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'receipt' => ['required_unless:booking_type,reserve', 'nullable', 'file', 'mimes:jpg,jpeg,png', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'receipt.required_unless' => 'Please upload your payment receipt so the admin can verify it.',
            'receipt.mimes' => 'The receipt must be a JPG or PNG image.',
            'receipt.max' => 'The receipt image must be 4 MB or smaller.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $room = Room::find($this->input('room_id'));

            if (! $room || $room->status !== 'available') {
                $validator->errors()->add('room_id', 'This room is not available for booking right now.');

                return;
            }

            if ((int) $this->input('guest_count') > $room->max_guests) {
                $validator->errors()->add('guest_count', "This room allows up to {$room->max_guests} guests.");
            }

            if (app(ReservationService::class)->hasConflict($room, $this->input('stay_date'))) {
                $validator->errors()->add('stay_date', 'This room is already reserved for that date. Please pick another date or room.');
            }
        });
    }
}
