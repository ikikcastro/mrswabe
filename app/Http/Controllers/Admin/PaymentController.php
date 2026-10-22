<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Approve a receipt and confirm its reservation atomically.
     */
    public function approve(Request $request, Payment $payment)
    {
        abort_unless($request->user()->hasPermissionTo('manage-reservations'), 403);

        DB::transaction(function () use ($payment) {
            $payment->markPaid();
            $payment->reservation()->update(['status' => 'confirmed']);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Payment approved and booking confirmed.',
                'payment_status' => 'paid',
                'booking_status' => 'confirmed',
            ]);
        }

        return back()->with('status', 'Payment approved and booking confirmed.');
    }

    /**
     * Reject a receipt and cancel its reservation, releasing the room date.
     */
    public function reject(Request $request, Payment $payment)
    {
        abort_unless($request->user()->hasPermissionTo('manage-reservations'), 403);

        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'failed']);
            $payment->reservation()->update(['status' => 'cancelled']);
        });

        return back()->with('status', 'Payment rejected and booking cancelled.');
    }

    public function markPaid(Request $request, Payment $payment)
    {
        abort_unless($request->user()->hasPermissionTo('manage-reservations'), 403);

        $request->validate([
            'reference_number' => ['nullable', 'string', 'max:255'],
        ]);

        $payment->markPaid($request->input('reference_number'));

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Payment marked as paid.',
                'payment_status' => 'paid',
            ]);
        }

        return back()->with('status', 'Payment marked as paid.');
    }

    public function markReservationPaid(Request $request, Reservation $reservation)
    {
        abort_unless($request->user()->hasPermissionTo('manage-reservations'), 403);

        $validated = $request->validate([
            'method' => ['required', 'in:gcash,maribank'],
        ]);

        DB::transaction(function () use ($reservation, $validated) {
            $reservation = Reservation::with(['room', 'payment'])
                ->lockForUpdate()
                ->findOrFail($reservation->id);

            abort_if($reservation->status === 'cancelled', 422);

            if ($reservation->payment?->status === 'paid') {
                return;
            }

            if ($reservation->payment) {
                $reservation->payment->markPaid();
                return;
            }

            $method = $reservation->payment_method ?? $validated['method'];
            $reservation->payment()->create([
                'amount' => $reservation->booking_price ?? $reservation->room->price,
                'method' => $method,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            if ($reservation->payment_method === null) {
                $reservation->update(['payment_method' => $method]);
            }
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Payment marked as paid.',
                'payment_status' => 'paid',
            ]);
        }

        return back()->with('status', 'Payment marked as paid.');
    }
}
