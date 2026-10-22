<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Validation\ValidationException;

/**
 * Centralizes reservation availability and stay timer lifecycle rules.
 */
class ReservationService
{
    public const STAY_OPTIONS = [3, 7, 24];

    public function hasConflict(Room $room, string $stayDate, ?int $excludingReservationId = null): bool
    {
        return Reservation::query()
            ->where('room_id', $room->id)
            ->whereDate('stay_date', $stayDate)
            ->whereNotIn('status', ['cancelled', 'checked_out'])
            ->when($excludingReservationId, fn ($query) => $query->where('id', '!=', $excludingReservationId))
            ->exists();
    }

    public function create(array $data): Reservation
    {
        $room = Room::findOrFail($data['room_id']);

        if ($this->hasConflict($room, $data['stay_date'])) {
            throw ValidationException::withMessages([
                'stay_date' => 'This room is already reserved for that date. Please pick another date or room.',
            ]);
        }

        return Reservation::create($data);
    }

    public function updateStatus(Reservation $reservation, string $status): Reservation
    {
        $attributes = ['status' => $status];

        if ($status === 'checked_in' && $reservation->status !== 'checked_in') {
            $startsAt = now();
            $stayHours = $reservation->duration_hours ?? $reservation->stay_hours;

            $attributes += [
                'checked_in_at' => $startsAt,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addHours((int) $stayHours),
                'reminder_sent_at' => null,
            ];
        }

        if ($status === 'checked_out') {
            $attributes['checked_out_at'] = now();
        }

        $reservation->update($attributes);

        return $reservation;
    }
}
