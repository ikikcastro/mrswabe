<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('manage-rooms') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('rooms', 'name')->ignore($this->route('room'))],
            'price_3_hours' => ['required', 'integer', 'min:0', 'max:1000000'],
            'price_7_hours' => ['required', 'integer', 'min:0', 'max:1000000'],
            'price_24_hours' => ['required', 'integer', 'min:0', 'max:1000000'],
            'max_guests' => ['sometimes', 'required', 'integer', 'min:1', 'max:20'],
            // Optional: leave empty to keep the current picture.
            'image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Another room already uses that name.',
            'image.mimes' => 'The picture must be a JPG, PNG or WebP image.',
            'image.max' => 'The picture must be 4 MB or smaller.',
        ];
    }
}