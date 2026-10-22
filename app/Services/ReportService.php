<?php

namespace App\Services;

class ReportService
{
    public function dashboard(): array
    {
        return [
            'stats' => ['total_revenue' => 45890.00, 'total_bookings' => 156, 'total_rooms' => 24, 'available_rooms' => 8, 'occupancy_rate' => 67, 'revenue_growth' => 12.5, 'booking_growth' => 8.3],
            'monthlyData' => ['labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], 'revenue' => [3200, 2800, 4100, 3800, 5200, 4800, 5600, 5100, 5900, 5400, 6200, 5800]],
            'roomOccupancy' => [['name' => 'Signature suites', 'occupied' => 6, 'total' => 8], ['name' => 'Comfort rooms', 'occupied' => 7, 'total' => 10], ['name' => 'Essentials', 'occupied' => 3, 'total' => 6]],
            'recentBookings' => [['guest' => 'John Doe', 'room' => 'The Moss Suite', 'check_in' => '2026-08-21', 'status' => 'confirmed', 'amount' => 13500.00], ['guest' => 'Sarah Smith', 'room' => 'Garden Room', 'check_in' => '2026-08-20', 'status' => 'checked_in', 'amount' => 9300.00], ['guest' => 'Mike Johnson', 'room' => 'The Nook', 'check_in' => '2026-08-22', 'status' => 'pending', 'amount' => 3600.00], ['guest' => 'Emily Brown', 'room' => 'The Penthouse', 'check_in' => '2026-08-25', 'status' => 'confirmed', 'amount' => 22000.00]],
            'todaySchedule' => [['time' => '10:00 AM', 'guest' => 'Sarah Smith', 'room' => 'Garden Room', 'type' => 'Check-in', 'status' => 'done'], ['time' => '11:30 AM', 'guest' => 'John Doe', 'room' => 'The Moss Suite', 'type' => 'Check-in', 'status' => 'upcoming'], ['time' => '12:00 PM', 'guest' => 'David Wilson', 'room' => 'Courtyard Room', 'type' => 'Check-out', 'status' => 'done'], ['time' => '02:30 PM', 'guest' => 'Emily Brown', 'room' => 'The Penthouse', 'type' => 'Check-in', 'status' => 'upcoming']],
        ];
    }
}
