<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->buildStats();
        $recentBookings = $this->buildRecentBookings();
        $roomOccupancy = $this->buildRoomOccupancy();
        $todaySchedule = $this->buildTodaySchedule();
        $monthlyData = $this->buildMonthlyRevenue();
        $allRooms = Room::orderBy('tier')->orderBy('name')->get();
        $reservedRooms = Reservation::with('room')->whereNull('payment_method')->latest()->get();
        $accounts = User::with('registeredBy')->latest()->take(50)->get();

        return view('admin.dashboard', compact(
            'stats', 'recentBookings', 'roomOccupancy', 'todaySchedule', 'monthlyData', 'allRooms', 'reservedRooms', 'accounts'
        ));
    }

    private function buildStats(): array
    {
        $totalRooms = Room::count();
        $availableRooms = Room::available()->count();

        $thisMonth = Reservation::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        $lastMonth = Reservation::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year);

        $totalBookings = (clone $thisMonth)->count();
        $prevBookings = max($lastMonth->count(), 1); // avoid divide-by-zero

        // Revenue now comes from actual confirmed *payments*, not a guess
        // based on the room's current price — this is the real amount
        // recorded at booking time, and only counts once the front desk
        // has confirmed the transfer (status = paid).
        $totalRevenue = (clone $thisMonth)
            ->whereHas('payment', fn ($q) => $q->where('status', 'paid'))
            ->with('payment')
            ->get()
            ->sum(fn ($r) => (float) ($r->payment?->amount ?? 0));

        $prevRevenue = max(
            Reservation::whereMonth('created_at', now()->subMonth()->month)
                ->whereYear('created_at', now()->subMonth()->year)
                ->whereHas('payment', fn ($q) => $q->where('status', 'paid'))
                ->with('payment')
                ->get()
                ->sum(fn ($r) => (float) ($r->payment?->amount ?? 0)),
            1
        );

        return [
            'total_revenue' => $totalRevenue,
            'revenue_growth' => round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100),
            'total_bookings' => $totalBookings,
            'booking_growth' => round((($totalBookings - $prevBookings) / $prevBookings) * 100),
            'available_rooms' => $availableRooms,
            'total_rooms' => $totalRooms,
            'occupancy_rate' => $totalRooms > 0
                ? round((($totalRooms - $availableRooms) / $totalRooms) * 100)
                : 0,
        ];
    }

    private function buildRecentBookings()
    {
        return Reservation::with(['room', 'payment'])
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Reservation $r) => [
                'reservation_id' => $r->id,
                'guest' => $r->guest_name,
                'guest_count' => $r->guest_count,
                'contact_number' => $r->contact_number,
                'room' => $r->room->name ?? 'Room removed',
                'check_in' => $r->stay_date->toDateString(),
                'duration_hours' => $r->duration_hours,
                'amount' => (float) ($r->payment?->amount ?? $r->booking_price ?? $r->room?->price ?? 0),
                'payment_method' => $r->payment_method ?? 'pay later',
                'payment_id' => $r->payment?->id,
                'payment_status' => $r->payment?->status ?? 'unpaid',
                'reference_number' => $r->payment?->reference_number,
                'receipt_url' => $r->payment?->receiptUrl(),
                'status' => $r->status,
            ]);
    }

    private function buildRoomOccupancy()
    {
        return Room::withCount([
            'reservations as occupied_count' => fn ($q) => $q->whereIn('status', ['confirmed', 'checked_in']),
        ])->get()->map(fn (Room $room) => [
            'name' => $room->name,
            'occupied' => $room->occupied_count,
            'total' => 1, // each room row represents one physical room
        ]);
    }

    private function buildTodaySchedule()
    {
        return Reservation::with('room')
            ->where(function ($query) {
                $query->whereDate('stay_date', Carbon::today())
                    ->orWhere('status', 'checked_in');
            })
            ->orderBy('created_at')
            ->get()
            ->map(function (Reservation $reservation) {
                $timeOutAt = $reservation->checked_in_at
                    ? $reservation->checked_in_at->copy()->addHours($reservation->duration_hours ?? 0)
                    : null;

                return [
                    'reservation_id' => $reservation->id,
                    'time' => ($reservation->checked_in_at ?? $reservation->created_at)->format('g:i A'),
                    'guest' => $reservation->guest_name,
                    'type' => ucfirst(str_replace('_', ' ', $reservation->status)),
                    'room' => $reservation->room->name ?? 'Room removed',
                    'status' => in_array($reservation->status, ['checked_in', 'checked_out']) ? 'done' : 'upcoming',
                    'can_time_out' => $reservation->status === 'checked_in'
                        && $reservation->duration_hours !== null
                        && $timeOutAt?->lte(now()),
                ];
            });
    }

    private function buildMonthlyRevenue(): array
    {
        $labels = collect(range(0, 5))
            ->map(fn ($i) => now()->subMonths(5 - $i))
            ->map(fn ($date) => $date->format('M'));

        $revenue = collect(range(0, 5))->map(function ($i) {
            $date = now()->subMonths(5 - $i);

            return Reservation::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->whereHas('payment', fn ($q) => $q->where('status', 'paid'))
                ->with('payment')
                ->get()
                ->sum(fn ($r) => (float) ($r->payment?->amount ?? 0));
        });

        return ['labels' => $labels->all(), 'revenue' => $revenue->all()];
    }
}
