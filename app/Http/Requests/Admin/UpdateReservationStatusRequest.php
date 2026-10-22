<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReservationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('manage-reservations') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,confirmed,checked_in,checked_out,cancelled'],
        ];
    }
}
