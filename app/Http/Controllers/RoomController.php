<?php

namespace App\Http\Controllers;

use App\Models\Room;

class RoomController extends Controller
{
    public function index()
    {
        $rooms = Room::available()
            ->orderBy('tier')
            ->get()
            ->map(fn (Room $room) => [
                'id' => $room->id,
                'tier' => $room->tier,
                'name' => $room->name,
                'max_guests' => $room->max_guests,
                // formatted string ("700"); the Blade view prints it with a ₱ prefix
                'price' => number_format($room->price_3_hours, 0),
                'duration_prices' => [
                    3 => (int) $room->price_3_hours,
                    7 => (int) $room->price_7_hours,
                    24 => (int) $room->price_24_hours,
                ],
                'blurb' => $room->blurb,
                // image_url handles both seeded links and pictures the admin uploaded
                'image' => $room->image_url,
                'tags' => $room->tags ?? [],
            ]);

        return view('rooms', compact('rooms'));
    }
}
