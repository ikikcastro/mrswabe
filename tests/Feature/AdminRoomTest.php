<?php

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('an admin can update all three room duration prices', function () {
    $permission = Permission::firstOrCreate(['name' => 'manage-rooms', 'guard_name' => 'web']);
    $ownerRole = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
    $ownerRole->givePermissionTo($permission);
    $owner = User::factory()->create();
    $owner->assignRole($ownerRole);

    $room = Room::create([
        'name' => 'Capacity Test Room',
        'tier' => 'comfort',
        'price' => 2500,
        'status' => 'available',
    ]);

    $this->actingAs($owner)
        ->put(route('admin.rooms.update', $room), [
            'name' => 'Capacity Test Room',
            'price_3_hours' => 800,
            'price_7_hours' => 1600,
            'price_24_hours' => 4000,
        ])
        ->assertRedirect()
        ->assertSessionHas('tab', 'rooms');

    expect($room->fresh()->price_3_hours)->toBe('800.00')
        ->and($room->fresh()->price_7_hours)->toBe('1600.00')
        ->and($room->fresh()->price_24_hours)->toBe('4000.00');
});