<?php

namespace App\Models;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * A booking with guest details, a selected stay package, and its check-in window.
 */
class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id', 'user_id', 'guest_name', 'contact_number',
        'stay_date', 'guest_count', 'duration_hours', 'booking_price', 'payment_method', 'status',
        'checked_in_at', 'checked_out_at', 'stay_hours', 'starts_at', 'ends_at', 'reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'stay_date' => 'date',
            'guest_count' => 'integer',
            'duration_hours' => 'integer',
            'booking_price' => 'decimal:2',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'stay_hours' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function isRunning(): bool
    {
        return $this->status === 'checked_in'
            && $this->starts_at !== null
            && $this->ends_at?->isFuture() === true;
    }
}
