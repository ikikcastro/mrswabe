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
            'price' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:available,maintenance'],
        ];
    }
}
