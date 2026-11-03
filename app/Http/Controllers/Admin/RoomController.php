<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoomRequest;
use App\Http\Requests\Admin\UpdateRoomRequest;
use App\Models\Room;
use App\Services\RoomService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/** Owner's Add/Edit/Delete room actions. */
class RoomController extends Controller
{
    public function __construct(private RoomService $rooms) {}

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

        return view('rooms.index', compact('rooms'));
    }

    public function store(StoreRoomRequest $request)
    {
        $data = $request->validated();

        foreach ([
            'price_3hrs' => 'price_3_hours',
            'price_7hrs' => 'price_7_hours',
            'price_24hrs' => 'price_24_hours',
        ] as $legacyKey => $databaseColumn) {
            $data[$databaseColumn] = $data[$databaseColumn] ?? $data[$legacyKey];
            unset($data[$legacyKey]);
        }

        $data['price'] = $data['price_3_hours'];

        if ($request->hasFile('image')) {
            $upload = cloudinary()->uploadApi()->upload(
                $request->file('image')->getRealPath(),
                ['folder' => 'rooms'],
            );
            $data['image'] = $upload['secure_url'];
        }

        $this->rooms->create($data);

        return back()->with('status', 'New room successfully added.')->with('tab', 'rooms');
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

            $upload = cloudinary()->uploadApi()->upload(
                $request->file('image')->getRealPath(),
                ['folder' => 'rooms'],
            );
            $data['image'] = $upload['secure_url'];

            // Clean up uploads made to the local public disk before Cloudinary was configured.
            if ($oldImage && ! Str::startsWith($oldImage, ['http://', 'https://'])) {
                Storage::disk('public')->delete($oldImage);
            }
        }

        $this->rooms->update($room, $data);

        return back()->with('status', 'Room updated.')->with('tab', 'rooms');
    }

    public function destroy(Room $room)
    {
        if ($room->image && ! Str::startsWith($room->image, ['http://', 'https://'])) {
            Storage::disk('public')->delete($room->image);
        }

        $this->rooms->delete($room);

        return back()->with('status', 'Room deleted.')->with('tab', 'rooms');
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
