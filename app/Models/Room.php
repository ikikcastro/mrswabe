<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A bookable room with a tier, prices for 3, 7, and 24 hours,
 * availability status, image, and blurb.
 */
class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'tier', 'price', 'price_3_hours', 'price_7_hours', 'price_24_hours',
        'price_3hrs', 'price_7hrs', 'price_24hrs',
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

    public function getPriceAttribute($value): string
    {
        return number_format((float) ($this->getAttribute('price_24_hours') ?? $value), 0);
    }

    public function getImageAttribute($value): ?string
    {
        if (! $value) {
            return null;
        }

        return Str::startsWith($value, ['http://', 'https://'])
            ? $value
            : Storage::disk('public')->url($value);
    }

    protected function price3hrs(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_3_hours,
            set: fn ($value) => ['price_3_hours' => $value],
        );
    }

    protected function price7hrs(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_7_hours,
            set: fn ($value) => ['price_7_hours' => $value],
        );
    }

    protected function price24hrs(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_24_hours,
            set: fn ($value) => ['price_24_hours' => $value],
        );
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
