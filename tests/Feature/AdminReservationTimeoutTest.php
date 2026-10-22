<?php

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function createReservationTimeoutFixture(array $reservationAttributes = []): array
{
    $permission = Permission::firstOrCreate(['name' => 'manage-reservations', 'guard_name' => 'web']);
    $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
    $ownerRole->givePermissionTo($permission);
    $owner = User::factory()->create();
    $owner->assignRole($ownerRole);

    $room = Room::create([
        'name' => 'Timeout Test Room',
        'tier' => 'comfort',
        'price' => 2500,
        'status' => 'available',
    ]);

    $reservation = Reservation::create(array_merge([
        'room_id' => $room->id,
        'guest_name' => 'Timeout Guest',
        'contact_number' => '09123456789',
        'stay_date' => today()->toDateString(),
        'duration_hours' => 3,
        'guest_count' => 1,
        'payment_method' => 'gcash',
        'status' => 'confirmed',
    ], $reservationAttributes));

    return [$owner, $reservation];
}

test('checking in a guest starts their stay timer using the selected duration', function () {
    [$owner, $reservation] = createReservationTimeoutFixture();
    $this->actingAs($owner);

    $this->patch(route('admin.reservations.updateStatus', $reservation), [
        'status' => 'checked_in',
    ])->assertRedirect();

    $checkedInReservation = $reservation->fresh();

    expect($checkedInReservation->checked_in_at)->not->toBeNull()
        ->and($checkedInReservation->starts_at->equalTo($checkedInReservation->checked_in_at))->toBeTrue()
        ->and($checkedInReservation->ends_at->equalTo($checkedInReservation->starts_at->copy()->addHours(3)))->toBeTrue();
});

test('the schedule offers a time out action after the room duration expires', function () {
    [$owner, $reservation] = createReservationTimeoutFixture([
        'status' => 'checked_in',
        'checked_in_at' => now()->subHours(4),
    ]);
    $this->actingAs($owner);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Time out')
        ->assertSee(route('admin.reservations.updateStatus', $reservation), false);
});

test('the schedule does not offer time out before the room duration expires', function () {
    [$owner] = createReservationTimeoutFixture([
        'status' => 'checked_in',
        'checked_in_at' => now()->subHours(2),
    ]);
    $this->actingAs($owner);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Time out');
});

test('timing out a guest records checkout time', function () {
    [$owner, $reservation] = createReservationTimeoutFixture([
        'status' => 'checked_in',
        'checked_in_at' => now()->subHours(4),
    ]);
    $this->actingAs($owner);

    $this->patch(route('admin.reservations.updateStatus', $reservation), [
        'status' => 'checked_out',
    ])->assertRedirect();

    expect($reservation->fresh()->status)->toBe('checked_out')
        ->and($reservation->fresh()->checked_out_at)->not->toBeNull();
});