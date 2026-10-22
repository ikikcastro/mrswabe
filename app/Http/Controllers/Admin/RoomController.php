<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Http\Requests\Admin\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
                // kept as a formatted string ("6,800") since the Blade view
                // prints it directly with a ₱ prefix, same as the old demo data
                'price' => number_format($room->price_3_hours, 0),
                'blurb' => $room->blurb,
                'image' => $room->image,
                'tags' => $room->tags ?? [],
            ]);

        return view('rooms', compact('rooms'));
    }

    public function store(StoreRoomRequest $request)
    {
        Room::create($request->validated());

        return back()->with('status', 'Room added.');
    }

    /**
     * Edit a room's name, nightly price and picture from the dashboard.
     * Tier and availability are left alone here (availability has its own
     * toggle). Existing bookings keep the price they were booked at, because
     * each payment stores its own amount.
     */
    public function update(UpdateRoomRequest $request, Room $room)
    {
        $data = $request->safe()->only([
            'name', 'price_3_hours', 'price_7_hours', 'price_24_hours', 'max_guests',
        ]);

        if ($request->hasFile('image')) {
            $oldImage = $room->image;

            $data['image'] = $request->file('image')->store('rooms', 'public');

            // Remove the previous picture only if it was an upload we stored,
            // never one of the seeded https:// links.
            if ($oldImage && ! Str::startsWith($oldImage, ['http://', 'https://'])) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        $room->update($data);

        return back()->with('status', "{$room->name} updated.")->with('tab', 'rooms');
    }

    public function destroy(Room $room)
    {
        $room->delete();

        return back()->with('status', 'Room removed.');
    }

    /**
     * Flip a room between available and maintenance. Kept separate from
     * update() so the admin dashboard can do this with a single button
     * instead of resubmitting the whole edit form (name/tier/price) just
     * to change one field.
     *
     * A room in 'maintenance' is excluded from RoomController@index's
     * Room::available() query on the customer side, so it simply stops
     * appearing on /rooms — no customer can select or book it.
     */
    public function toggleStatus(Request $request, Room $room)
    {
        abort_unless($request->user()->hasPermissionTo('manage-rooms'), 403);

        $room->update([
            'status' => $room->status === 'available' ? 'maintenance' : 'available',
        ]);

        return back()
            ->with('status', "{$room->name} is now marked as {$room->status}.")
            ->with('tab', 'rooms');
    }
}
