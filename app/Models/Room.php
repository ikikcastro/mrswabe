<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'tier', 'price', 'price_3_hours', 'price_7_hours', 'price_24_hours',
        'max_guests', 'blurb', 'image', 'tags', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_3_hours' => 'decimal:2',
            'price_7_hours' => 'decimal:2',
            'price_24_hours' => 'decimal:2',
            'tags' => 'array',
        ];
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * The picture to show in the browser. The seeded rooms store a full
     * https:// link; rooms whose picture the admin uploaded store a path on
     * the "public" disk (e.g. rooms/abc123.jpg). This handles both.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->image) {
                return null;
            }

            return Str::startsWith($this->image, ['http://', 'https://'])
                ? $this->image
                : Storage::disk('public')->url($this->image);
        });
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
