<?php

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the all-level filter initially shows all available room tiers', function () {
    foreach (['signature', 'comfort', 'essential'] as $tier) {
        Room::create([
            'name' => ucfirst($tier).' Room',
            'tier' => $tier,
            'price' => 2500,
            'status' => 'available',
        ]);
    }

    Room::create([
        'name' => 'Maintenance Room',
        'tier' => 'essential',
        'price' => 2500,
        'status' => 'maintenance',
    ]);

    $this->get(route('rooms.index'))
        ->assertOk()
        ->assertSee('data-filter="all" aria-pressed="true"', false)
        ->assertSee('data-tier="signature"', false)
        ->assertSee('data-tier="comfort"', false)
        ->assertSee('data-tier="essential"', false)
        ->assertDontSee('Maintenance Room');
});
