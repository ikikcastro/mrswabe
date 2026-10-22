<?php

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function createPaymentReviewFixture(): array
{
    $room = Room::create([
        'name' => 'Review Test Room',
        'tier' => 'comfort',
        'price' => 2500,
        'status' => 'available',
    ]);
    $reservation = Reservation::create([
        'room_id' => $room->id,
        'guest_name' => 'Review Guest',
        'contact_number' => '09123456789',
        'stay_date' => today()->addDay()->toDateString(),
        'payment_method' => 'gcash',
        'status' => 'pending',
    ]);
    $payment = Payment::create([
        'reservation_id' => $reservation->id,
        'amount' => $room->price,
        'method' => 'gcash',
        'status' => 'pending',
    ]);
    $permission = Permission::firstOrCreate(['name' => 'manage-reservations', 'guard_name' => 'web']);
    $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
    $ownerRole->givePermissionTo($permission);
    $owner = User::factory()->create();
    $owner->assignRole($ownerRole);

    return [$owner, $payment, $reservation];
}

test('approving a payment marks it paid and confirms its reservation', function () {
    [$owner, $payment, $reservation] = createPaymentReviewFixture();
    $this->actingAs($owner);

    $response = $this->patch(route('admin.payments.approve', $payment));

    $response->assertRedirect();
    expect($payment->fresh()->status)->toBe('paid')
        ->and($reservation->fresh()->status)->toBe('confirmed')
        ->and($payment->fresh()->paid_at)->not->toBeNull();
});

    test('approving a payment returns updated statuses for the dashboard', function () {
        [$owner, $payment] = createPaymentReviewFixture();
        $this->actingAs($owner);

        $this->patchJson(route('admin.payments.approve', $payment))
        ->assertOk()
        ->assertJsonPath('payment_status', 'paid')
        ->assertJsonPath('booking_status', 'confirmed');
    });

test('rejecting a payment marks it failed and cancels its reservation', function () {
    [$owner, $payment, $reservation] = createPaymentReviewFixture();
    $this->actingAs($owner);

    $response = $this->patch(route('admin.payments.reject', $payment));

    $response->assertRedirect();
    expect($payment->fresh()->status)->toBe('failed')
        ->and($reservation->fresh()->status)->toBe('cancelled');
});

test('the dashboard renders review actions for pending payments', function () {
    [$owner] = createPaymentReviewFixture();
    $this->actingAs($owner);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Approve')
        ->assertSee('Reject');
});

test('the dashboard shows the reservation duration and selected package price', function () {
    [$owner, , $existingReservation] = createPaymentReviewFixture();
    $reservation = Reservation::create([
        'room_id' => $existingReservation->room_id,
        'guest_name' => 'Duration Booking Guest',
        'contact_number' => '09123456789',
        'stay_date' => today()->addDays(2)->toDateString(),
        'guest_count' => 2,
        'duration_hours' => 7,
        'booking_price' => 1400,
        'payment_method' => 'gcash',
        'status' => 'pending',
    ]);
    Payment::create([
        'reservation_id' => $reservation->id,
        'amount' => 1400,
        'method' => 'gcash',
        'status' => 'pending',
    ]);
    $this->actingAs($owner);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('7 hours')
        ->assertSee('1,400');
});

test('the dashboard lists unpaid reservations and labels them unpaid', function () {
    [$owner, , $existingReservation] = createPaymentReviewFixture();
    Reservation::create([
        'room_id' => $existingReservation->room_id,
        'guest_name' => 'Pay Later Guest',
        'contact_number' => '09123456789',
        'stay_date' => today()->addDays(2)->toDateString(),
        'payment_method' => null,
        'status' => 'cancelled',
    ]);
    $this->actingAs($owner);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('1 reservations')
        ->assertSee('Pay Later Guest')
        ->assertSee('Unpaid');
});

test('the dashboard offers a mark-paid action for active unpaid reservations', function () {
    [$owner, , $existingReservation] = createPaymentReviewFixture();
    $reservation = Reservation::create([
        'room_id' => $existingReservation->room_id,
        'guest_name' => 'Pay Later Guest',
        'contact_number' => '09123456789',
        'stay_date' => today()->addDays(2)->toDateString(),
        'payment_method' => null,
        'status' => 'pending',
    ]);
    $this->actingAs($owner);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Mark paid')
        ->assertSee(route('admin.reservations.markPaid', $reservation), false)
        ->assertSee('aria-label="Payment method"', false);
});

test('an admin can mark an unpaid reservation paid and record its payment method', function () {
    [$owner, , $existingReservation] = createPaymentReviewFixture();
    $reservation = Reservation::create([
        'room_id' => $existingReservation->room_id,
        'guest_name' => 'Pay Later Guest',
        'contact_number' => '09123456789',
        'stay_date' => today()->addDays(2)->toDateString(),
        'payment_method' => null,
        'status' => 'pending',
    ]);
    $this->actingAs($owner);

    $response = $this->patchJson(route('admin.reservations.markPaid', $reservation), [
        'method' => 'maribank',
    ]);

    $response->assertOk()->assertJsonPath('payment_status', 'paid');
    $payment = Payment::where('reservation_id', $reservation->id)->firstOrFail();
    expect($payment->amount)->toBe('2500.00')
        ->and($payment->method)->toBe('maribank')
        ->and($payment->status)->toBe('paid')
        ->and($payment->paid_at)->not->toBeNull()
        ->and($reservation->fresh()->payment_method)->toBe('maribank');
});