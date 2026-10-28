<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function __construct(private ReservationService $reservations) {}

    /** My bookings: the logged-in customer's reservations, with live stay timers. */
    public function index(Request $request)
    {
        return view('bookings.index', [
            'reservations' => Reservation::with('room')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->get(),
        ]);
    }

    public function store(StoreBookingRequest $request)
    {
        $room = Room::findOrFail($request->validated('room_id'));
        $isReserveOnly = $request->validated('booking_type') === 'reserve';
        $durationHours = (int) $request->validated('duration_hours');
        $bookingPrice = match ($durationHours) {
            3 => $room->price_3_hours,
            7 => $room->price_7_hours,
            24 => $room->price_24_hours,
        };

        // Files are stored on the public disk under a generated name.
        $receiptPath = (! $isReserveOnly && $request->hasFile('receipt'))
            ? $request->file('receipt')->store('receipts', 'public')
            : null;

        $reservation = DB::transaction(function () use ($request, $room, $isReserveOnly, $receiptPath, $durationHours, $bookingPrice) {
            $reservation = $this->reservations->create([
                'room_id' => $room->id,
                'user_id' => Auth::id(),
                'guest_name' => $request->validated('guest_name'),
                'contact_number' => $request->validated('contact_number'),
                'stay_date' => $request->validated('stay_date'),
                'duration_hours' => $durationHours,
                'stay_hours' => $durationHours,
                'booking_price' => $bookingPrice,
                'guest_count' => $request->validated('guest_count'),
                'payment_method' => $isReserveOnly ? null : $request->validated('payment_method'),
                'status' => 'pending',
            ]);

            if (! $isReserveOnly) {
                // The payment remains pending until an admin marks it paid.
                Payment::create([
                    'reservation_id' => $reservation->id,
                    'amount' => $bookingPrice,
                    'method' => $request->validated('payment_method'),
                    'status' => 'pending',
                    'reference_number' => $request->validated('reference_number'),
                    'receipt_path' => $receiptPath,
                ]);
            }

            return $reservation;
        });

        return response()->json([
            'message' => $isReserveOnly ? 'Room reserved.' : 'Booking request received.',
            'reservation_id' => $reservation->id,
        ], 201);
    }
}
