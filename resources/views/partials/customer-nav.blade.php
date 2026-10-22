{{--
    Customer top navigation, shared by the Rooms and My bookings pages.
    Usage: @include('partials.customer-nav', ['active' => 'rooms'])  (or 'bookings')

    For logged-in customers it also shows:
      - a live countdown chip while they have a stay in progress
      - a bell with unread notifications (including the "30 minutes remaining" notice)
--}}
@php($active = $active ?? 'rooms')

<header class="site-nav" id="customerNav"
        data-csrf="{{ csrf_token() }}"
        data-feed="{{ url('/notifications') }}"
        data-read="{{ url('/notifications/read') }}">
    <a href="{{ url('/') }}" class="nav-brand">
        <span class="brand-mark brand-mark--sm" aria-hidden="true">HH</span>
        <span class="nav-brand-name">ikik House</span>
    </a>

    <nav class="nav-links" aria-label="Primary">
        <a href="{{ url('/rooms') }}" class="nav-link {{ $active === 'rooms' ? 'is-active' : '' }}">Rooms</a>
        <a href="{{ url('/bookings') }}" class="nav-link {{ $active === 'bookings' ? 'is-active' : '' }}">My bookings</a>
        <a href="{{ url('/amenities') }}" class="nav-link">Amenities</a>
    </nav>

    <div class="nav-user">
        @auth
            <a href="{{ url('/bookings') }}" class="stay-chip" id="stayChip" hidden>
                <span class="stay-chip-dot" aria-hidden="true"></span>
                <span id="stayChipTime">--:--:--</span>
            </a>

            <div class="notif">
                <button type="button" class="notif-bell" id="notifBell" aria-label="Notifications" aria-expanded="false">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 9a6 6 0 1 1 12 0c0 5 2 6.5 2 6.5H4S6 14 6 9z"></path>
                        <path d="M10 19a2 2 0 0 0 4 0"></path>
                    </svg>
                    <span class="notif-badge" id="notifBadge" hidden>0</span>
                </button>

                <div class="notif-panel" id="notifPanel" hidden>
                    <p class="notif-heading">Notifications</p>
                    <ul class="notif-list" id="notifList"></ul>
                </div>
            </div>
        @endauth

        <span class="nav-greeting">Welcome back, {{ Auth::user()->name ?? 'guest' }}</span>
        <form action="/logout" method="POST" class="nav-logout-form">
            @csrf
            <button class="button button-dark button-sm" type="submit">Logout</button>
        </form>
    </div>
</header>

<div class="stay-toast" id="stayToast" role="status" aria-live="polite"></div>

@auth
    <script>
        (function () {
            const nav = document.getElementById('customerNav');
            const bell = document.getElementById('notifBell');
            if (!nav || !bell) return;

            const badge = document.getElementById('notifBadge');
            const panel = document.getElementById('notifPanel');
            const list = document.getElementById('notifList');
            const chip = document.getElementById('stayChip');
            const chipTime = document.getElementById('stayChipTime');
            const toast = document.getElementById('stayToast');

            const feedUrl = nav.dataset.feed;
            const readUrl = nav.dataset.read;
            const csrf = nav.dataset.csrf;
            const WARN_MS = 30 * 60 * 1000;

            let endsAt = null;
            let toastTimer;
            let toasted = new Set(JSON.parse(sessionStorage.getItem('toastedNotifs') || '[]'));

            const pad = (n) => String(n).padStart(2, '0');
            function format(ms) {
                const s = Math.max(0, Math.floor(ms / 1000));
                return pad(Math.floor(s / 3600)) + ':' + pad(Math.floor((s % 3600) / 60)) + ':' + pad(s % 60);
            }

            // ----- Live countdown chip (ticks every second, no server calls) -----
            function tick() {
                if (!endsAt) { chip.hidden = true; return; }
                const left = endsAt - Date.now();
                chip.hidden = false;
                chip.classList.toggle('is-warning', left <= WARN_MS);
                chipTime.textContent = left <= 0 ? "Time's up" : format(left) + ' left';
            }
            setInterval(tick, 1000);

            // ----- Toast -----
            function showToast(title, message) {
                toast.replaceChildren();
                const strong = document.createElement('strong');
                strong.textContent = title;
                const span = document.createElement('span');
                span.textContent = message;
                toast.append(strong, span);
                toast.classList.add('is-open');
                clearTimeout(toastTimer);
                toastTimer = setTimeout(() => toast.classList.remove('is-open'), 9000);
            }

            // ----- Render the feed -----
            function render(data) {
                badge.textContent = data.unread_count;
                badge.hidden = data.unread_count === 0;

                list.replaceChildren();
                if (!data.notifications.length) {
                    const empty = document.createElement('li');
                    empty.className = 'notif-empty';
                    empty.textContent = 'No notifications yet.';
                    list.appendChild(empty);
                }
                data.notifications.forEach((n) => {
                    const li = document.createElement('li');
                    li.className = 'notif-item' + (n.read ? '' : ' is-unread');
                    const title = document.createElement('strong');
                    title.textContent = n.title;
                    const msg = document.createElement('p');
                    msg.textContent = n.message;
                    const when = document.createElement('small');
                    when.textContent = n.when;
                    li.append(title, msg, when);
                    list.appendChild(li);
                });

                endsAt = data.active_stay ? new Date(data.active_stay.ends_at).getTime() : null;
                tick();

                // Pop a toast once per new unread notification, so the 30-minute
                // notice is noticed even when the panel is closed.
                data.notifications.filter((n) => !n.read && !toasted.has(n.id)).forEach((n) => {
                    toasted.add(n.id);
                    showToast(n.title, n.message);
                });
                sessionStorage.setItem('toastedNotifs', JSON.stringify([...toasted]));
            }

            async function load() {
                try {
                    const res = await fetch(feedUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                    if (res.ok) render(await res.json());
                } catch (e) { /* offline or logged out: try again on the next poll */ }
            }

            load();
            setInterval(load, 30000);
            document.addEventListener('visibilitychange', () => { if (!document.hidden) load(); });

            // ----- Bell open / close (opening marks everything as read) -----
            bell.addEventListener('click', async (e) => {
                e.stopPropagation();
                const opening = panel.hidden;
                panel.hidden = !opening;
                bell.setAttribute('aria-expanded', String(opening));

                if (opening && !badge.hidden) {
                    badge.hidden = true;
                    try {
                        await fetch(readUrl, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                            credentials: 'same-origin',
                        });
                    } catch (err) { /* badge will correct itself on the next poll */ }
                }
            });

            document.addEventListener('click', (e) => {
                if (!panel.hidden && !panel.contains(e.target)) {
                    panel.hidden = true;
                    bell.setAttribute('aria-expanded', 'false');
                }
            });
        })();
    </script>
@endauth
