<x-page>
    @slot('title')
        Owner dashboard | ikik House
    @endslot

    @slot('bodyClass')
        admin-body
    @endslot

    <div class="admin-shell">
        <aside class="admin-sidebar" id="adminSidebar">
            <div>
                <div class="admin-sidebar-heading">
                    <a href="{{ route('admin.dashboard') }}" class="admin-brand"><span>ikik</span> House</a>
                    <button class="admin-sidebar-close" id="adminSidebarClose" type="button" aria-label="Close operations navigation">&#8592;</button>
                </div>
                <p class="admin-sidebar-label">Operations</p>
                <nav class="admin-nav" aria-label="Manager navigation">
                    <a class="is-active" href="#overview" data-dashboard-tab="overview" aria-current="page">Overview</a>
                    <a href="#bookings" data-dashboard-tab="bookings">Bookings <b>{{ count($recentBookings) }}</b></a>
                    <a href="#reservations" data-dashboard-tab="reservations">Reservations <b>{{ count($reservedRooms) }}</b></a>
                    <a href="#rooms" data-dashboard-tab="rooms">Rooms <b>{{ $stats['available_rooms'] }}</b></a>
                    <a href="#schedule" data-dashboard-tab="schedule">Today's schedule</a>
                </nav>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="admin-logout" type="submit">Sign out <span aria-hidden="true">&#8594;</span></button>
            </form>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="admin-header-title">
                    <button class="admin-menu-toggle" id="adminMenuToggle" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="adminSidebar">&#9776;</button>
                    <div>
                        <p class="admin-kicker">{{ now()->format('l, F j, Y') }}</p>
                        <h1>Good morning, {{ Auth::user()->name }}.</h1>
                    </div>
                </div>
            </header>

            @if (session('status'))
                <div class="admin-alert admin-alert--ok">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="admin-alert">{{ $errors->first() }}</div>
            @endif

            <div class="admin-tab-panels">
                <section class="admin-tab-panel is-active" data-dashboard-panel="overview">
                    <div class="admin-intro">
                        <div><p class="admin-kicker">Owner overview</p><h2>The house at a glance.</h2></div>
                    </div>
                    <section class="admin-stats" aria-label="Key performance indicators">
                        <article class="admin-stat"><span class="admin-stat-label">Revenue this month</span><strong>&#8369;{{ number_format($stats['total_revenue'], 0) }}</strong><small class="{{ $stats['revenue_growth'] >= 0 ? 'is-positive' : '' }}">{{ $stats['revenue_growth'] >= 0 ? '+' : '' }}{{ $stats['revenue_growth'] }}% vs last month</small></article>
                        <article class="admin-stat"><span class="admin-stat-label">Bookings</span><strong>{{ $stats['total_bookings'] }}</strong><small class="{{ $stats['booking_growth'] >= 0 ? 'is-positive' : '' }}">{{ $stats['booking_growth'] >= 0 ? '+' : '' }}{{ $stats['booking_growth'] }}% vs last month</small></article>
                        <article class="admin-stat"><span class="admin-stat-label">Rooms available</span><strong>{{ $stats['available_rooms'] }}<em>/ {{ $stats['total_rooms'] }}</em></strong><small>Ready to receive guests</small></article>
                        <article class="admin-stat"><span class="admin-stat-label">Occupancy rate</span><strong>{{ $stats['occupancy_rate'] }}%</strong><small>Across all room types</small></article>
                    </section>
                    <section class="admin-content-grid">
                        <article class="admin-panel admin-revenue-panel">
                            <div class="admin-panel-heading"><div><p class="admin-kicker">Performance</p><h3>Revenue overview</h3></div><span class="admin-period">This year</span></div>
                            <div class="admin-chart"><div class="chart-y-labels"><span>&#8369;7k</span><span>&#8369;4k</span><span>&#8369;0</span></div><div class="chart-bars" aria-label="Monthly revenue chart">@foreach ($monthlyData['revenue'] as $value)<div class="chart-bar-wrap"><div class="chart-bar" style="height: {{ max(8, ($value / 7000) * 100) }}%" title="&#8369;{{ number_format($value) }}"></div><span>{{ $monthlyData['labels'][$loop->index] }}</span></div>@endforeach</div></div>
                        </article>
                    </section>
                </section>

                <section class="admin-tab-panel" data-dashboard-panel="rooms" hidden>
                    <div class="admin-intro">
                        <div><p class="admin-kicker">Tonight</p><h2>Room occupancy.</h2></div>
                    </div>

                    <article class="admin-panel">
                        <div class="admin-panel-heading">
                            <div><p class="admin-kicker">Live rooms</p><h3>Room occupancy</h3></div>
                            <span class="admin-panel-icon">&#9673;</span>
                        </div>
                        <div class="occupancy-list">
                            @foreach ($roomOccupancy as $room)
                                <div class="occupancy-row">
                                    <div><span>{{ $room['name'] }}</span><strong>{{ $room['occupied'] }}/{{ $room['total'] }}</strong></div>
                                    <div class="occupancy-track"><span style="width: {{ ($room['occupied'] / $room['total']) * 100 }}%"></span></div>
                                </div>
                            @endforeach
                        </div>
                    </article>

                    <div class="admin-intro" style="margin-top: 2.5rem;">
                        <div><p class="admin-kicker">Manage</p><h2>Room availability.</h2></div>
                    </div>

                    <article class="admin-panel">
                        <div class="admin-panel-heading">
                            <div><p class="admin-kicker">All rooms</p><h3>Manage rooms</h3></div>
                            <span class="admin-count">{{ count($allRooms) }} rooms</span>
                        </div>
                        <div class="room-manage-list">
                            @forelse ($allRooms as $room)
                                <div class="room-manage-row">
                                    <div class="room-manage-info">
                                        <strong>{{ $room->name }}</strong>
                                        <span>{{ ['signature' => 'Level 1', 'comfort' => 'Level 2', 'essential' => 'Level 3'][$room->tier] ?? ucfirst($room->tier) }} &middot; 3 hrs &#8369;{{ number_format($room->price_3_hours, 0) }} &middot; 7 hrs &#8369;{{ number_format($room->price_7_hours, 0) }} &middot; 24 hrs &#8369;{{ number_format($room->price_24_hours, 0) }}</span>
                                    </div>
                                    <span class="room-status-badge room-status-badge--{{ $room->status }}">
                                        {{ $room->status === 'available' ? 'Bookable' : 'Unavailable' }}
                                    </span>
                                    <button type="button" class="button button-sm button-outline room-edit-btn"
                                        data-update-url="{{ route('admin.rooms.update', $room) }}"
                                        data-name="{{ $room->name }}"
                                        data-price-three-hours="{{ (int) $room->price_3_hours }}"
                                        data-price-seven-hours="{{ (int) $room->price_7_hours }}"
                                        data-price-twenty-four-hours="{{ (int) $room->price_24_hours }}"
                                        data-max-guests="{{ $room->max_guests }}"
                                        data-image="{{ $room->image_url }}">Edit</button>
                                    <form method="POST" action="{{ route('admin.rooms.toggleStatus', $room) }}" class="room-manage-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="button button-sm {{ $room->status === 'available' ? 'button-dark' : 'button-light' }}">
                                            {{ $room->status === 'available' ? 'Mark unavailable' : 'Mark available' }}
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <p class="booking-empty">No rooms yet.</p>
                            @endforelse
                        </div>
                    </article>
                </section>

                <section class="admin-tab-panel" data-dashboard-panel="schedule" hidden>
                    <div class="admin-intro"><div><p class="admin-kicker">Today</p><h2>Today's schedule.</h2></div><p>{{ count($todaySchedule) }} guest events are on the calendar.</p></div>
                    <article class="admin-panel"><div class="admin-panel-heading"><div><p class="admin-kicker">Front desk</p><h3>Schedule</h3></div><span class="admin-count">{{ count($todaySchedule) }} events</span></div><div class="schedule-list">
                        @foreach ($todaySchedule as $event)
                            <div class="schedule-row">
                                <time>{{ $event['time'] }}</time>
                                <div><strong>{{ $event['guest'] }}</strong><span>{{ $event['type'] }} / {{ $event['room'] }}</span></div>
                                @if ($event['can_time_out'])
                                    <form method="POST" action="{{ route('admin.reservations.updateStatus', $event['reservation_id']) }}" onsubmit="return confirm('Time out this guest and mark the reservation checked out?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="checked_out">
                                        <button type="submit" class="button button-sm button-dark">Time out</button>
                                    </form>
                                @endif
                                <i class="schedule-dot schedule-dot--{{ $event['status'] }}"></i>
                            </div>
                        @endforeach
                    </div></article>
                </section>

                <section class="admin-tab-panel" data-dashboard-panel="bookings" hidden>
                    <div class="admin-intro">
                        <div><p class="admin-kicker">Latest activity</p><h2>Recent bookings.</h2></div>
                    </div>
                    <article class="admin-panel">
                        <div class="admin-panel-heading"><div><p class="admin-kicker">Guests</p><h3>Recent bookings</h3></div><span class="admin-count">{{ count($recentBookings) }} guests</span></div>
                        <div class="booking-list">
                            @forelse ($recentBookings as $booking)
                                <div class="booking-row">
                                    <div class="booking-avatar">{{ substr($booking['guest'], 0, 1) }}</div>
                                    <div>
                                        <strong>{{ $booking['guest'] }}</strong>
                                        <span>{{ $booking['contact_number'] }} &middot; {{ $booking['guest_count'] }} {{ \Illuminate\Support\Str::plural('guest', $booking['guest_count']) }}@if ($booking['duration_hours']) &middot; {{ $booking['duration_hours'] }} {{ \Illuminate\Support\Str::plural('hour', $booking['duration_hours']) }}@endif &middot; {{ $booking['room'] }} / {{ \Carbon\Carbon::parse($booking['check_in'])->format('M d') }}</span>
                                        @if ($booking['reference_number'])
                                            <span class="booking-reference">Ref: {{ $booking['reference_number'] }}</span>
                                        @endif
                                        @if ($booking['receipt_url'])
                                            <a class="receipt-link" href="{{ $booking['receipt_url'] }}" target="_blank" rel="noopener">
                                                <img src="{{ $booking['receipt_url'] }}" alt="Payment receipt" class="receipt-thumb" loading="lazy">
                                                <span>View receipt</span>
                                            </a>
                                        @endif
                                    </div>
                                    <div class="booking-meta">
                                        <strong>&#8369;{{ number_format($booking['amount'], 0) }}</strong>
                                        <span class="booking-payment">{{ strtoupper($booking['payment_method']) }}</span>
                                        <span class="booking-status booking-status--{{ $booking['status'] }}">{{ str_replace('_', ' ', $booking['status']) }}</span>
                                        <span class="payment-status-badge payment-status-badge--{{ $booking['payment_status'] }}">{{ ucfirst($booking['payment_status']) }}</span>
                                        @if ($booking['payment_status'] === 'pending' && $booking['payment_id'])
                                            <div class="approval-actions">
                                                <form method="POST" action="{{ route('admin.payments.approve', $booking['payment_id']) }}" data-payment-action="approve">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="button button-sm button-dark">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.payments.reject', $booking['payment_id']) }}" onsubmit="return confirm('Reject this payment and cancel the booking?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="button button-sm button-outline">Reject</button>
                                                </form>
                                            </div>
                                        @elseif ($booking['payment_status'] === 'unpaid' && $booking['status'] !== 'cancelled')
                                            <form method="POST" action="{{ route('admin.reservations.markPaid', $booking['reservation_id']) }}" class="mark-paid-form" data-payment-action="paid">
                                                @csrf
                                                @method('PATCH')
                                                @if (in_array($booking['payment_method'], ['gcash', 'maribank'], true))
                                                    <input type="hidden" name="method" value="{{ $booking['payment_method'] }}">
                                                @else
                                                    <select name="method" class="mark-paid-input" aria-label="Payment method" required>
                                                        <option value="" selected disabled>Method</option>
                                                        <option value="gcash">GCash</option>
                                                        <option value="maribank">MariBank</option>
                                                    </select>
                                                @endif
                                                <button type="submit" class="button button-sm button-dark">Mark paid</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="booking-empty">No bookings yet — once a guest books a room, it'll show up here.</p>
                            @endforelse
                        </div>
                    </article>
                </section>

                <section class="admin-tab-panel" data-dashboard-panel="reservations" hidden>
                    <div class="admin-intro">
                        <div><p class="admin-kicker">Held rooms</p><h2>Reservations.</h2></div>
                    </div>

                    <article class="admin-panel">
                        <div class="admin-panel-heading">
                            <div><p class="admin-kicker">Reserved</p><h3>Reserved rooms</h3></div>
                            <span class="admin-count">{{ count($reservedRooms) }} reservations</span>
                        </div>

                        <div class="reservation-list">
                            @forelse ($reservedRooms as $reservation)
                                <div class="reservation-row">
                                    <div class="booking-avatar">{{ substr($reservation->guest_name, 0, 1) }}</div>
                                    <div class="reservation-info">
                                        <strong>{{ $reservation->guest_name }}</strong>
                                        <span>{{ $reservation->contact_number }}</span>
                                        <span>{{ $reservation->room->name ?? 'Room removed' }} &middot; {{ $reservation->guest_count }} {{ \Illuminate\Support\Str::plural('guest', $reservation->guest_count) }}@if ($reservation->duration_hours) &middot; {{ $reservation->duration_hours }} {{ \Illuminate\Support\Str::plural('hour', $reservation->duration_hours) }}@endif &middot; {{ $reservation->stay_date->format('M d, Y') }}</span>
                                    </div>

                                    <div class="reservation-actions">
                                        <span class="booking-status booking-status--{{ $reservation->status }}">{{ str_replace('_', ' ', $reservation->status) }}</span>

                                        @php
                                            $nextActions = match ($reservation->status) {
                                                'pending' => ['confirmed' => 'Confirm', 'cancelled' => 'Cancel'],
                                                'confirmed' => ['checked_in' => 'Check in', 'cancelled' => 'Cancel'],
                                                'checked_in' => ['checked_out' => 'Check out'],
                                                default => [],
                                            };
                                        @endphp

                                        @foreach ($nextActions as $status => $label)
                                            <form method="POST" action="{{ route('admin.reservations.updateStatus', $reservation) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $status }}">
                                                <button type="submit" class="button button-sm {{ $status === 'cancelled' ? 'button-outline' : 'button-dark' }}">{{ $label }}</button>
                                            </form>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                            @endforelse
                        </div>
                    </article>
                </section>

                <section class="admin-tab-panel" data-dashboard-panel="accounts" hidden>
                    <div class="admin-intro">
                        <div><p class="admin-kicker">Customer access</p><h2>Accounts.</h2></div>
                    </div>

                    <article class="admin-panel">
                        <div class="admin-panel-heading">
                            <div><p class="admin-kicker">New customer</p><h3>Create account</h3></div>
                        </div>
                        <form method="POST" action="{{ route('admin.accounts.store') }}" class="admin-form">
                            @csrf
                            <input type="hidden" name="_tab" value="accounts">
                            <label for="account-name">Full name</label>
                            <input id="account-name" name="name" type="text" value="{{ old('name') }}" maxlength="255" required>
                            <label for="account-email">Email address</label>
                            <input id="account-email" name="email" type="email" value="{{ old('email') }}" maxlength="255" required>
                            <label for="account-password">Password</label>
                            <input id="account-password" name="password" type="password" required>
                            <label for="account-password-confirmation">Confirm password</label>
                            <input id="account-password-confirmation" name="password_confirmation" type="password" required>
                            <button type="submit" class="admin-button admin-button--dark">Create customer account</button>
                        </form>
                    </article>

                    <article class="admin-panel" style="margin-top: 1.25rem;">
                        <div class="admin-panel-heading">
                            <div><p class="admin-kicker">Registration history</p><h3>Recent accounts</h3></div>
                            <span class="admin-count">Latest {{ count($accounts) }}</span>
                        </div>
                        <div class="account-list">
                            @forelse ($accounts as $account)
                                <div class="account-row">
                                    <div class="booking-avatar">{{ substr($account->name, 0, 1) }}</div>
                                    <div class="account-info">
                                        <strong>{{ $account->name }}</strong>
                                        <span>{{ $account->email }}</span>
                                    </div>
                                    <div class="account-registrar">
                                        <span>Registered by</span>
                                        <strong>
                                            @if ($account->registered_by_user_id === $account->id)
                                                {{ $account->name }} (self-registered)
                                            @else
                                                {{ $account->registeredBy?->name ?? 'Not recorded (legacy account)' }}
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            @empty
                                <p class="booking-empty">No accounts found.</p>
                            @endforelse
                        </div>
                    </article>
                </section>
            </div>
        </main>
    </div>

    <div class="room-edit-modal" id="roomEditModal" aria-hidden="true">
        <div class="room-edit-backdrop" id="roomEditBackdrop"></div>
        <div class="room-edit-panel" role="dialog" aria-modal="true" aria-labelledby="roomEditTitle">
            <button type="button" class="room-edit-close" id="roomEditClose" aria-label="Close">&#10005;</button>
            <p class="admin-kicker">Edit room</p>
            <h3 id="roomEditTitle">Room details</h3>

            <form method="POST" enctype="multipart/form-data" id="roomEditForm" class="room-edit-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="_tab" value="rooms">

                <label for="roomEditName">Room name</label>
                <input id="roomEditName" name="name" type="text" maxlength="255" required>

                <label for="roomEditPrice3Hours">Price for 3 hours (&#8369;)</label>
                <input id="roomEditPrice3Hours" name="price_3_hours" type="number" min="0" max="1000000" step="1" required>

                <label for="roomEditPrice7Hours">Price for 7 hours (&#8369;)</label>
                <input id="roomEditPrice7Hours" name="price_7_hours" type="number" min="0" max="1000000" step="1" required>

                <label for="roomEditPrice24Hours">Price for 24 hours (&#8369;)</label>
                <input id="roomEditPrice24Hours" name="price_24_hours" type="number" min="0" max="1000000" step="1" required>

                <label for="roomEditImage">Room picture</label>
                <img id="roomEditPreview" class="room-edit-preview" src="" alt="Current room picture" hidden>
                <label class="room-edit-upload" for="roomEditImage">
                    <span id="roomEditImageName">Choose a new picture (JPG, PNG or WebP)</span>
                </label>
                <input id="roomEditImage" name="image" type="file" accept="image/png,image/jpeg,image/webp" hidden>
                <p class="room-edit-hint">Leave this empty to keep the current picture.</p>

                <button type="submit" class="admin-button admin-button--dark">Save changes</button>
            </form>
        </div>
    </div>

    <script>
    (() => {
        const modal = document.getElementById('roomEditModal');
        const form = document.getElementById('roomEditForm');
        const nameInput = document.getElementById('roomEditName');
        const price3HoursInput = document.getElementById('roomEditPrice3Hours');
        const price7HoursInput = document.getElementById('roomEditPrice7Hours');
        const price24HoursInput = document.getElementById('roomEditPrice24Hours');
        const imageInput = document.getElementById('roomEditImage');
        const imageName = document.getElementById('roomEditImageName');
        const preview = document.getElementById('roomEditPreview');
        const defaultLabel = 'Choose a new picture (JPG, PNG or WebP)';
        let objectUrl = null;

        const close = () => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        };

        document.querySelectorAll('.room-edit-btn').forEach((button) => {
            button.addEventListener('click', () => {
                form.action = button.dataset.updateUrl;
                nameInput.value = button.dataset.name;
                price3HoursInput.value = button.dataset.priceThreeHours;
                price7HoursInput.value = button.dataset.priceSevenHours;
                price24HoursInput.value = button.dataset.priceTwentyFourHours;
                imageInput.value = '';
                imageName.textContent = defaultLabel;

                preview.hidden = !button.dataset.image;
                preview.src = button.dataset.image || '';

                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                nameInput.focus();
            });
        });

        imageInput.addEventListener('change', () => {
            const file = imageInput.files[0];
            if (objectUrl) URL.revokeObjectURL(objectUrl);

            if (!file) {
                imageName.textContent = defaultLabel;
                return;
            }

            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.hidden = false;
            imageName.textContent = file.name;
        });

        document.getElementById('roomEditClose').addEventListener('click', close);
        document.getElementById('roomEditBackdrop').addEventListener('click', close);
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });

        @if (session('tab') === 'rooms' || old('_tab') === 'rooms')
            document.querySelector('[data-dashboard-tab="rooms"]')?.click();
        @endif
    })();
    </script>
</x-page>
