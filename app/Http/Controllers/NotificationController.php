<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;

/** JSON feed behind the notification bell and stay countdown in the customer nav. */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $stay = Reservation::with("room")
            ->where("user_id", $user->id)
            ->where("status", "checked_in")
            ->where("ends_at", ">", now())
            ->orderBy("ends_at")
            ->first();

        return response()->json([
            "unread_count" => $user->unreadNotifications()->count(),
            "notifications" => $user->notifications()->latest()->limit(10)->get()->map(fn ($n) => [
                "id" => $n->id,
                "title" => $n->data["title"] ?? "Notification",
                "message" => $n->data["message"] ?? "",
                "read" => $n->read_at !== null,
                "when" => $n->created_at->diffForHumans(),
            ]),
            "active_stay" => $stay ? [
                "room" => $stay->room?->name,
                "ends_at" => $stay->ends_at->toIso8601String(),
            ] : null,
        ]);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->noContent();
    }
}
