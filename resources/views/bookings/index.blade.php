<x-page>
    @slot('title')
        ikik House | My bookings
    @endslot

    @slot('bodyClass')
        rooms-body
    @endslot

    @php
        $statusLabel = [
            'pending' => 'Pending', 'confirmed' => 'Confirmed',
            'checked_in' => 'Checked in', 'checked_out' => 'Checked out', 'cancelled' => 'Cancelled',
        ];
    @endphp

    @include('partials.customer-nav', ['active' => 'bookings'])

    <main class="bookings-page">
        <p class="eyebrow bookings-eyebrow">My bookings</p>
        <h1 class="bookings-title">Your stays</h1>

        @forelse ($reservations as $r)
            @php($stayHours = $r->duration_hours ?? $r->stay_hours)
            <article class="booking-card {{ $r->isRunning() ? 'is-running' : '' }}">
                <div class="booking-card-head">
                    <h2>{{ $r->room?->name ?? 'Room' }}</h2>
                    <span class="status-pill status-pill--{{ $r->status }}">{{ $statusLabel[$r->status] ?? ucfirst($r->status) }}</span>
                </div>

                <p class="booking-meta">
                    {{ $stayHours }}-hour stay
                    @if ($r->starts_at && $r->ends_at)
                        &middot; {{ $r->starts_at->format('M j, g:i A') }} &rarr; {{ $r->ends_at->format('M j, g:i A') }}
                    @endif
                </p>

                @if ($r->isRunning())
                    <div class="stay-clock"
                         data-starts="{{ $r->starts_at->toIso8601String() }}"
                         data-ends="{{ $r->ends_at->toIso8601String() }}">
                        <p class="stay-clock-label">Time remaining</p>
                        <p class="stay-clock-time" data-clock>--:--:--</p>
                        <div class="stay-progress"><span data-progress></span></div>
                        <p class="stay-clock-note" data-note>Your stay ends at {{ $r->ends_at->format('g:i A') }}.</p>
                    </div>
                @elseif (in_array($r->status, ['pending', 'confirmed']))
                    <p class="booking-hint">Your {{ $stayHours }}-hour timer starts when the front desk checks you in.</p>
                @endif
            </article>
        @empty
            <p class="rooms-empty">No bookings yet. <a href="{{ url('/rooms') }}">Browse rooms</a></p>
        @endforelse
    </main>

    <script>
        (function () {
            const WARN_MS = 30 * 60 * 1000;
            const pad = (n) => String(n).padStart(2, '0');
            const format = (ms) => {
                const s = Math.max(0, Math.floor(ms / 1000));
                return pad(Math.floor(s / 3600)) + ':' + pad(Math.floor((s % 3600) / 60)) + ':' + pad(s % 60);
            };

            const clocks = [...document.querySelectorAll('.stay-clock')];
            if (!clocks.length) return;

            function update() {
                clocks.forEach((el) => {
                    const starts = new Date(el.dataset.starts).getTime();
                    const ends = new Date(el.dataset.ends).getTime();
                    const left = ends - Date.now();
                    const done = Math.min(100, Math.max(0, ((Date.now() - starts) / (ends - starts)) * 100));

                    el.querySelector('[data-clock]').textContent = format(left);
                    el.querySelector('[data-progress]').style.width = done + '%';
                    el.classList.toggle('is-warning', left <= WARN_MS);

                    const note = el.querySelector('[data-note]');
                    if (left <= 0) note.textContent = "Time's up. Please check out with the front desk.";
                    else if (left <= WARN_MS) note.textContent = '30 minutes or less remaining.';
                });
            }

            update();
            setInterval(update, 1000);
        })();
    </script>
</x-page>
