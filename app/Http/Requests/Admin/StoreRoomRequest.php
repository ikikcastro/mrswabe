<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo('manage-rooms') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tier' => ['required', 'in:signature,comfort,essential'],
            'price_3hrs' => ['required_without:price_3_hours', 'numeric', 'min:0'],
            'price_7hrs' => ['required_without:price_7_hours', 'numeric', 'min:0'],
            'price_24hrs' => ['required_without:price_24_hours', 'numeric', 'min:0'],
            'price_3_hours' => ['required_without:price_3hrs', 'numeric', 'min:0'],
            'price_7_hours' => ['required_without:price_7hrs', 'numeric', 'min:0'],
            'price_24_hours' => ['required_without:price_24hrs', 'numeric', 'min:0'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'status' => ['required', 'in:available,maintenance'],
            'blurb' => ['nullable', 'string', 'max:500'],
        ];
    }
}
