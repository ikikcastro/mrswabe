<?php

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function createBookingRoom(): Room
{
    return Room::create([
        'name' => 'Test Room',
        'tier' => 'comfort',
        'price' => 2500,
        'status' => 'available',
    ]);
}

function bookingPayload(Room $room, array $attributes = []): array
{
    return array_merge([
        'room_id' => $room->id,
        'guest_name' => 'Test Guest',
        'stay_date' => today()->addDay()->toDateString(),
        'guest_count' => 1,
        'duration_hours' => 3,
        'contact_number' => '09123456789',
    ], $attributes);
}

test('a reserve booking does not require or create a payment', function () {
    $room = createBookingRoom();
    $this->actingAs(User::factory()->create());

    $response = $this->postJson('/reservations', bookingPayload($room, [
        'booking_type' => 'reserve',
        'guest_count' => 2,
    ]));

    $response->assertCreated()
        ->assertJsonPath('message', 'Room reserved.');

    $reservation = Reservation::firstOrFail();

    expect($reservation->payment_method)->toBeNull()
        ->and($reservation->guest_count)->toBe(2)
        ->and($reservation->duration_hours)->toBe(3)
        ->and($reservation->booking_price)->toBe('700.00')
        ->and(Payment::count())->toBe(0);
});

test('the selected booking duration determines its payment price', function (int $durationHours, int $price) {
    $room = createBookingRoom();
    $this->actingAs(User::factory()->create());
    Storage::fake('public');

    $response = $this->post('/reservations', bookingPayload($room, [
        'duration_hours' => $durationHours,
        'payment_method' => 'gcash',
        'receipt' => UploadedFile::fake()->create('receipt.png', 1, 'image/png'),
    ]), ['Accept' => 'application/json']);

    $response->assertCreated();
    expect(Reservation::firstOrFail()->booking_price)->toBe(number_format($price, 2, '.', ''))
        ->and(Payment::firstOrFail()->amount)->toBe(number_format($price, 2, '.', ''));
})->with([
    '3 hours' => [3, 700],
    '7 hours' => [7, 1400],
    '24 hours' => [24, 3500],
]);

test('a customized room duration price is charged to the booking', function () {
    $room = createBookingRoom();
    $room->update(['price_7_hours' => 1900]);
    $this->actingAs(User::factory()->create());
    Storage::fake('public');

    $response = $this->post('/reservations', bookingPayload($room, [
        'duration_hours' => 7,
        'payment_method' => 'gcash',
        'receipt' => UploadedFile::fake()->create('receipt.png', 1, 'image/png'),
    ]), ['Accept' => 'application/json']);

    $response->assertCreated();
    expect(Reservation::firstOrFail()->booking_price)->toBe('1900.00')
        ->and(Payment::firstOrFail()->amount)->toBe('1900.00');
});

test('a booking cannot exceed the room guest limit', function () {
    $room = createBookingRoom();
    $room->update(['max_guests' => 2]);
    $this->actingAs(User::factory()->create());

    $response = $this->postJson('/reservations', bookingPayload($room, [
        'booking_type' => 'reserve',
        'guest_count' => 3,
    ]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('guest_count');

    expect(Reservation::count())->toBe(0);
});

test('older pay-now bookings still work without a booking type', function () {
    $room = createBookingRoom();
    $this->actingAs(User::factory()->create());
    Storage::fake('public');

    $response = $this->post('/reservations', bookingPayload($room, [
        'payment_method' => 'gcash',
        'receipt' => UploadedFile::fake()->create('receipt.png', 1, 'image/png'),
    ]), ['Accept' => 'application/json']);

    $response->assertCreated();

    expect(Reservation::firstOrFail()->payment_method)->toBe('gcash')
        ->and(Payment::firstOrFail()->method)->toBe('gcash');

    Storage::disk('public')->assertExists(Payment::firstOrFail()->receipt_path);
});

test('pay-now bookings require a receipt', function () {
    $room = createBookingRoom();
    $this->actingAs(User::factory()->create());

    $response = $this->postJson('/reservations', bookingPayload($room, [
        'payment_method' => 'gcash',
    ]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('receipt');

    expect(Reservation::count())->toBe(0)
        ->and(Payment::count())->toBe(0);
});

test('a room cannot be reserved twice for the same date', function () {
    $room = createBookingRoom();
    $stayDate = today()->addDay()->toDateString();
    Reservation::create([
        'room_id' => $room->id,
        'guest_name' => 'Existing Guest',
        'contact_number' => '09123456789',
        'stay_date' => $stayDate,
        'payment_method' => 'gcash',
        'status' => 'confirmed',
    ]);
    $this->actingAs(User::factory()->create());

    $response = $this->postJson('/reservations', bookingPayload($room, [
        'stay_date' => $stayDate,
        'booking_type' => 'reserve',
    ]));

    $response->assertUnprocessable()
        ->assertJsonValidationErrors('stay_date');
});

test('the reservation service rejects a conflicting room and date', function () {
    $room = createBookingRoom();
    Reservation::create([
        'room_id' => $room->id,
        'guest_name' => 'Existing Guest',
        'contact_number' => '09123456789',
        'stay_date' => today()->addDay()->toDateString(),
        'payment_method' => 'gcash',
        'status' => 'confirmed',
    ]);

    expect(fn () => app(ReservationService::class)->create([
        'room_id' => $room->id,
        'guest_name' => 'New Guest',
        'contact_number' => '09123456789',
        'stay_date' => today()->addDay()->toDateString(),
        'duration_hours' => 3,
        'stay_hours' => 3,
        'payment_method' => 'gcash',
        'status' => 'pending',
    ]))->toThrow(ValidationException::class);
});

test('the bookings page only shows the signed-in customer bookings with a stay clock', function () {
    $room = createBookingRoom();
    $otherRoom = Room::create([
        'name' => 'Other Test Room',
        'tier' => 'essential',
        'price' => 1800,
        'status' => 'available',
    ]);
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $startsAt = now()->subHour();

    $reservation = Reservation::create([
        'room_id' => $room->id,
        'user_id' => $user->id,
        'guest_name' => 'Test Guest',
        'contact_number' => '09123456789',
        'stay_date' => today()->toDateString(),
        'duration_hours' => 3,
        'stay_hours' => 3,
        'guest_count' => 1,
        'payment_method' => 'gcash',
        'status' => 'checked_in',
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->copy()->addHours(3),
    ]);

    Reservation::create([
        'room_id' => $otherRoom->id,
        'user_id' => $otherUser->id,
        'guest_name' => 'Other Guest',
        'contact_number' => '09123456789',
        'stay_date' => today()->toDateString(),
        'duration_hours' => 7,
        'stay_hours' => 7,
        'guest_count' => 1,
        'payment_method' => 'gcash',
        'status' => 'confirmed',
    ]);

    $this->actingAs($user)
        ->get(route('bookings.index'))
        ->assertOk()
        ->assertSee('Test Room')
        ->assertSee('3-hour stay')
        ->assertSee('data-clock', false)
        ->assertDontSee('Other Test Room');

    expect($reservation->isRunning())->toBeTrue();
});
