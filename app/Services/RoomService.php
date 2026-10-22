<?php

namespace App\Services;

use App\Models\Room;

class RoomService
{
    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        return array_map(
            static fn (Room $room): array => $room->toArray(),
            [
                new Room(['id' => 1, 'tier' => 'signature', 'name' => 'The Moss Suite', 'price' => '6,800', 'blurb' => 'Corner suite with a soaking tub and skyline views.', 'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1600&q=70', 'tags' => ['King bed', 'Soaking tub', 'Skyline view']]),
                new Room(['id' => 2, 'tier' => 'comfort', 'name' => 'Garden Room', 'price' => '3,400', 'blurb' => 'Warm, quiet, and just the right size for two.', 'image' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=1600&q=70', 'tags' => ['Queen bed', 'Garden view', 'Work desk']]),
                new Room(['id' => 3, 'tier' => 'essential', 'name' => 'The Nook', 'price' => '1,650', 'blurb' => 'A well-kept single room for the traveler who just needs rest.', 'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1600&q=70', 'tags' => ['Single bed', 'Fast Wi-Fi', 'Free coffee']]),
                new Room(['id' => 4, 'tier' => 'signature', 'name' => 'The Penthouse', 'price' => '9,200', 'blurb' => 'Top-floor privacy with a private terrace and butler call.', 'image' => 'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1600&q=70', 'tags' => ['Private terrace', 'Butler service', 'Rain shower']]),
                new Room(['id' => 5, 'tier' => 'comfort', 'name' => 'Courtyard Room', 'price' => '3,100', 'blurb' => 'Opens onto the inner courtyard, quiet even at noon.', 'image' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1600&q=70', 'tags' => ['Twin beds', 'Courtyard access', 'Reading nook']]),
                new Room(['id' => 6, 'tier' => 'essential', 'name' => 'Budget Bunk', 'price' => '1,200', 'blurb' => 'Simple, clean, and easy on the wallet without cutting comfort.', 'image' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?auto=format&fit=crop&w=1600&q=70', 'tags' => ['Bunk bed', 'Shared lounge', 'Locker']]),
            ],
        );
    }
}
