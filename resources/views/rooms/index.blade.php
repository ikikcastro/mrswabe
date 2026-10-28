<x-page>
    @slot('title')
        ikik House | Rooms
    @endslot

    @slot('bodyClass')
        rooms-body
    @endslot

    @include('partials.customer-nav', ['active' => 'rooms'])

    <section class="rooms-hero">
        <p class="eyebrow">Your rooms</p>
        <h1>Find the stay that fits your mood <span class="hero-and">&amp;</span> your budget.</h1>
    </section>

    {{-- $rooms comes from RoomController@index (real DB rows) --}}

    <section class="room-slideshow" id="roomSlideshow" aria-label="Featured rooms">
        @foreach ($rooms as $index => $room)
            <div class="slide {{ $index === 0 ? 'is-active' : '' }}" data-index="{{ $index }}">
                <button type="button" class="slide-image-btn" data-lightbox-src="{{ $room['image'] }}" data-lightbox-alt="{{ $room['name'] }}">
                    <img src="{{ $room['image'] }}" alt="{{ $room['name'] }}" loading="{{ $index === 0 ? 'eager' : 'lazy' }}">
                </button>
                <div class="slide-scrim" aria-hidden="true"></div>
                <div class="slide-overlay">
                    <span class="tier-badge tier-badge--{{ $room['tier'] }}">{{ ['signature' => 'Level 1', 'comfort' => 'Level 2', 'essential' => 'Level 3'][$room['tier']] ?? ucfirst($room['tier']) }}</span>
                    <h2>{{ $room['name'] }}</h2>
                    <p>{{ $room['blurb'] }}</p>
                    <button type="button" class="button button-light explore-btn" data-room='@json($room)'>
                        Explore <span aria-hidden="true">&#8594;</span>
                    </button>
                </div>
            </div>
        @endforeach
        <div class="slideshow-dots" id="slideshowDots">
            @foreach ($rooms as $index => $room)
                <button type="button" class="slideshow-dot {{ $index === 0 ? 'is-active' : '' }}" data-goto="{{ $index }}" aria-label="Show {{ $room['name'] }}"></button>
            @endforeach
        </div>
        <p class="slideshow-hint">Tap a photo to view it fullscreen</p>
    </section>

    <section class="rooms-filter-wrap" aria-label="Filter rooms">
        <p class="eyebrow filter-eyebrow">Browse all rooms</p>
        <div class="rooms-filter" id="roomsFilter" data-active="all">
            <button type="button" class="filter-pill is-active" data-filter="all" aria-pressed="true">All Level</button>
            <button type="button" class="filter-pill" data-filter="signature" aria-pressed="false">Level 1</button>
            <button type="button" class="filter-pill" data-filter="comfort" aria-pressed="false">Level 2</button>
            <button type="button" class="filter-pill" data-filter="essential" aria-pressed="false">Level 3</button>
        </div>
    </section>

    <section class="rooms-grid" id="roomsGrid" aria-label="Available rooms">
        @foreach ($rooms as $index => $room)
            <article class="room-card reveal" data-tier="{{ $room['tier'] }}" style="--reveal-delay: {{ ($index % 3) * 0.1 }}s">
                <div class="room-media">
                    <button type="button" class="slide-image-btn" data-lightbox-src="{{ $room['image'] }}" data-lightbox-alt="{{ $room['name'] }}">
                        <img src="{{ $room['image'] }}" alt="{{ $room['name'] }}" loading="lazy">
                    </button>
                    <span class="tier-badge tier-badge--{{ $room['tier'] }}">{{ ['signature' => 'Level 1', 'comfort' => 'Level 2', 'essential' => 'Level 3'][$room['tier']] ?? ucfirst($room['tier']) }}</span>
                </div>
                <div class="room-body">
                    <h3>{{ $room['name'] }}</h3>
                    <p class="room-blurb">{{ $room['blurb'] }}</p>
                    <ul class="room-tags">
                        @foreach ($room['tags'] as $tag)
                            <li>{{ $tag }}</li>
                        @endforeach
                    </ul>
                    <div class="room-footer">
                        <div class="room-price">
                            <span class="price-amount">&#8369;{{ $room['price'] }}</span>
                            <span class="price-unit">/ 3 hours</span>
                        </div>
                        <button type="button" class="button button-light room-cta explore-btn" data-room='@json($room)'>Explore <span aria-hidden="true">&#8594;</span></button>
                    </div>
                </div>
            </article>
        @endforeach
    </section>

    <div class="lightbox" id="lightbox" aria-hidden="true">
        <button type="button" class="lightbox-close" id="lightboxClose" aria-label="Close fullscreen image">&#10005;</button>
        <img src="" alt="" id="lightboxImage">
    </div>

    <div class="explore-modal" id="exploreModal" aria-hidden="true">
        <div class="explore-modal-backdrop" id="exploreModalBackdrop"></div>
        <div class="explore-modal-panel" role="dialog" aria-modal="true" aria-labelledby="exploreModalTitle">
            <button type="button" class="explore-modal-close" id="exploreModalClose" aria-label="Close room details">&#10005;</button>
            <div class="explore-modal-media">
                <img src="" alt="" id="exploreModalImage">
                <span class="tier-badge" id="exploreModalTier"></span>
            </div>
            <div class="explore-modal-body">
                <h3 id="exploreModalTitle"></h3>
                <p class="explore-modal-blurb" id="exploreModalBlurb"></p>
                <p class="explore-modal-capacity" id="exploreModalCapacity"></p>
                <div class="explore-modal-price"><span class="price-amount" id="exploreModalPrice"></span><span class="price-unit">/ 3 hours</span></div>
                <p class="explore-modal-label">What's included</p>
                <ul class="explore-modal-tags" id="exploreModalTags"></ul>
                <button type="button" class="button button-dark" id="exploreModalBook">Book this room</button>
            </div>
        </div>
    </div>

    <div class="booking-modal" id="bookingModal" aria-hidden="true">
        <div class="explore-modal-backdrop" id="bookingModalBackdrop"></div>
        <div class="booking-modal-panel" role="dialog" aria-modal="true" aria-labelledby="bookingModalTitle">
            <button type="button" class="explore-modal-close" id="bookingModalClose" aria-label="Close booking form">&#10005;</button>

            {{-- Step 1: guest details --}}
            <div class="booking-step is-active" id="bookingFormStep">
                <p class="eyebrow" id="bookingModalRoomName"></p>
                <h3 id="bookingModalTitle">Book this room</h3>
                <p class="booking-copy">Send your details and choose how you'd like to pay.</p>
                <form class="auth-form" id="bookingForm">
                    <label for="bookingFullname">Full name</label>
                    <input id="bookingFullname" name="guest_name" type="text" value="{{ Auth::user()?->name ?? '' }}" required>
                    <label for="bookingDuration">Room duration</label>
                    <select id="bookingDuration" name="duration_hours" required>
                        @foreach (config('booking.room_duration_hours') as $hours)
                            <option value="{{ $hours }}">{{ $hours }} hours</option>
                        @endforeach
                    </select>
                    <div class="booking-duration-total"><span>Total price</span><strong id="bookingSelectedPrice">&#8369;700</strong></div>
                    <label for="bookingDate">Stay date</label>
                    <input id="bookingDate" name="stay_date" type="date" required>
                    <label for="bookingContact">Contact number</label>
                    <input id="bookingContact" name="contact_number" type="tel" placeholder="09XX XXX XXXX" required>
                    <button type="submit" class="button button-dark booking-submit">Continue to payment <span aria-hidden="true">&#8594;</span></button>
                    <button type="button" class="button button-outline booking-reserve" id="bookingReserveBtn">Reserve room (pay later)</button>
                    <p class="booking-reserve-note">Reserving holds the room for your date. You can pay afterwards.</p>
                </form>
            </div>

            {{-- Step 2: pick a payment method — logo buttons instead of a dropdown --}}
            <div class="booking-step" id="bookingPaymentStep">
                <p class="eyebrow">Payment</p>
                <h3>Choose how to pay</h3>
                <p class="booking-copy">Tap GCash or MariBank to get a QR code you can scan.</p>
                <div class="payment-method-grid">
                    <button type="button" class="payment-method-btn payment-method-btn--gcash" data-method="gcash">
                        <span class="payment-method-logo">GCash</span>
                    </button>
                    <button type="button" class="payment-method-btn payment-method-btn--maribank" data-method="maribank">
                        <span class="payment-method-logo">MariBank</span>
                    </button>
                </div>
                <button type="button" class="booking-back-link" id="bookingBackToForm">&#8592; Back to details</button>
            </div>

            {{-- Step 3: QR code to scan and pay --}}
            <div class="booking-step" id="bookingQrStep">
                <p class="eyebrow" id="bookingQrMethodLabel">GCash</p>
                <h3>Scan to pay</h3>
                <p class="booking-copy">Open <span id="bookingQrMethodName">GCash</span>, scan this code, and send the amount below. Enter your reference number once you're done (optional — you can also add it later).</p>
                <div class="payment-qr-wrap">
                    <img id="paymentQrImage" src="" alt="Payment QR code" width="200" height="200">
                    <p id="paymentQrMissingNote" class="payment-qr-missing" hidden></p>
                </div>
                <div class="payment-qr-details">
                    <div><span>Send to</span><strong>09708760469</strong></div>
                    <div><span>Amount</span><strong id="paymentQrAmount">&#8369;0</strong></div>
                </div>
                <label for="bookingReferenceNumber">Reference number (optional)</label>
                <input id="bookingReferenceNumber" name="reference_number" type="text" placeholder="e.g. 1234567890">
                <label for="bookingReceipt">Upload your payment receipt</label>
                <label class="receipt-upload" for="bookingReceipt">
                    <span id="bookingReceiptName">Choose a screenshot of your receipt (JPG or PNG)</span>
                </label>
                <input id="bookingReceipt" name="receipt" type="file" accept="image/png,image/jpeg" hidden>
                <p class="booking-reserve-note">The admin checks this receipt before approving your booking.</p>
                <button type="button" class="button button-dark booking-submit" id="bookingConfirmPayment">I've paid <span aria-hidden="true">&#8594;</span></button>
                <button type="button" class="booking-back-link" id="bookingBackToPayment">&#8592; Choose a different method</button>
            </div>

            <div class="booking-step" id="bookingSendStep">
                <div class="envelope-scene" aria-hidden="true">
                    <div class="envelope-box">
                        <div class="box-flap-back"></div>
                        <div class="envelope" id="envelopeEl">
                            <div class="envelope-back"></div>
                            <div class="envelope-flap"></div>
                            <div class="envelope-front"></div>
                        </div>
                        <div class="box-flap-front"></div>
                    </div>
                </div>
                <p class="booking-sending-text" id="bookingSendingText">Sending your booking request...</p>
            </div>
            <div class="booking-step" id="bookingDoneStep">
                <div class="booking-done-check" aria-hidden="true">
                    <svg viewBox="0 0 52 52"><circle cx="26" cy="26" r="24"></circle><path d="M15 27l7 7 15-15"></path></svg>
                </div>
                <h3>Request sent</h3>
                <p class="booking-copy" id="bookingDoneCopy">We've received your request and will follow up shortly.</p>
                <button type="button" class="button button-light" id="bookingDoneClose">Done</button>
            </div>
        </div>
    </div>

    <script>
        (() => {
            const slideshow = document.getElementById('roomSlideshow');
            const slides = slideshow?.querySelectorAll('.slide') || [];
            const dots = document.querySelectorAll('.slideshow-dot');
            let currentSlide = 0;
            let autoplay;

            function goToSlide(index) {
                slides[currentSlide]?.classList.remove('is-active');
                dots[currentSlide]?.classList.remove('is-active');
                currentSlide = index;
                slides[currentSlide]?.classList.add('is-active');
                dots[currentSlide]?.classList.add('is-active');
            }

            function startAutoplay() {
                window.clearInterval(autoplay);
                if (slides.length > 1) {
                    autoplay = window.setInterval(() => goToSlide((currentSlide + 1) % slides.length), 4500);
                }
            }

            dots.forEach((dot, index) => {
                dot.addEventListener('click', () => {
                    goToSlide(index);
                    startAutoplay();
                });
            });
            startAutoplay();

            const closeLightbox = () => {
                lightbox?.classList.remove('is-open');
                lightbox?.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('no-scroll');
            };

            document.querySelectorAll('.slide-image-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    lightboxImage.src = button.dataset.lightboxSrc;
                    lightboxImage.alt = button.dataset.lightboxAlt || '';
                    lightbox.classList.add('is-open');
                    lightbox.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('no-scroll');
                });
            });
            document.getElementById('lightboxClose')?.addEventListener('click', closeLightbox);
            lightbox?.addEventListener('click', (event) => {
                if (event.target === lightbox) closeLightbox();
            });

            const modal = document.getElementById('exploreModal');
            const modalImage = document.getElementById('exploreModalImage');
            const modalTier = document.getElementById('exploreModalTier');
            const modalTitle = document.getElementById('exploreModalTitle');
            const modalBlurb = document.getElementById('exploreModalBlurb');
            const modalCapacity = document.getElementById('exploreModalCapacity');
            const modalPrice = document.getElementById('exploreModalPrice');
            const modalTags = document.getElementById('exploreModalTags');
            const bookingModal = document.getElementById('bookingModal');
            const bookingFormStep = document.getElementById('bookingFormStep');
            const bookingPaymentStep = document.getElementById('bookingPaymentStep');
            const bookingQrStep = document.getElementById('bookingQrStep');
            const bookingSendStep = document.getElementById('bookingSendStep');
            const bookingDoneStep = document.getElementById('bookingDoneStep');
            const bookingForm = document.getElementById('bookingForm');
            const bookingFormFullname = document.getElementById('bookingFullname');
            const bookingDuration = document.getElementById('bookingDuration');
            const bookingSelectedPrice = document.getElementById('bookingSelectedPrice');
            const envelope = document.getElementById('envelopeEl');
            const sendingText = document.getElementById('bookingSendingText');
            const bookingRoomName = document.getElementById('bookingModalRoomName');
            const bookingDoneCopy = document.getElementById('bookingDoneCopy');
            const paymentQrImage = document.getElementById('paymentQrImage');
            const paymentQrAmount = document.getElementById('paymentQrAmount');
            const paymentQrMissingNote = document.getElementById('paymentQrMissingNote');
            const bookingQrMethodLabel = document.getElementById('bookingQrMethodLabel');
            const bookingQrMethodName = document.getElementById('bookingQrMethodName');
            const bookingReferenceNumber = document.getElementById('bookingReferenceNumber');
            const PAYMENT_NUMBER = '09708760469';
            const GCASH_QR_DATA_URI = 'data:image/png;base64,{{ base64_encode(file_get_contents(public_path('images/gcash-qr.png'))) }}';
            const DEFAULT_GUEST_COUNT = 1;
            let selectedRoom = null;
            let selectedMethod = null;

            function updateBookingPrice() {
                const price = Number(selectedRoom.duration_prices[bookingDuration.value]);
                const formatter = new Intl.NumberFormat('en-PH');
                bookingSelectedPrice.textContent = `₱${formatter.format(price)}`;
                bookingDuration.querySelectorAll('option').forEach((option) => {
                    const optionPrice = selectedRoom.duration_prices[option.value];
                    option.textContent = `${option.value} hours - ₱${formatter.format(optionPrice)}`;
                });
            }

            bookingDuration.addEventListener('change', updateBookingPrice);

            function closeModal() {
                modal?.classList.remove('is-open');
                modal?.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('no-scroll');
            }

            function closeBookingModal() {
                bookingModal?.classList.remove('is-open');
                bookingModal?.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('no-scroll');
            }

            function showStep(step) {
                [bookingFormStep, bookingPaymentStep, bookingQrStep, bookingSendStep, bookingDoneStep]
                    .forEach((el) => el.classList.remove('is-active'));
                step.classList.add('is-active');
            }

            async function bookingErrorMessage(response) {
                try {
                    const data = await response.json();
                    const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
                    return firstError || data.message || null;
                } catch (error) {
                    return null;
                }
            }

            document.getElementById('bookingDate')?.setAttribute('min', new Date().toISOString().split('T')[0]);

            document.getElementById('bookingReserveBtn')?.addEventListener('click', async () => {
                if (!bookingForm.reportValidity()) return;

                const reserveButton = document.getElementById('bookingReserveBtn');
                reserveButton.disabled = true;

                try {
                    const response = await fetch('{{ route('rooms.book') }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({
                            booking_type: 'reserve',
                            room_id: selectedRoom.id,
                            guest_name: bookingFormFullname.value,
                            guest_count: DEFAULT_GUEST_COUNT,
                            duration_hours: Number(bookingDuration.value),
                            stay_date: document.getElementById('bookingDate').value,
                            contact_number: document.getElementById('bookingContact').value,
                        }),
                    });

                    if (!response.ok) {
                        bookingDoneCopy.textContent = (await bookingErrorMessage(response))
                            || 'We could not reserve the room. Please try again.';
                        showStep(bookingDoneStep);
                        return;
                    }

                    bookingDoneCopy.textContent = `Thanks, ${bookingFormFullname.value}. ${selectedRoom.name} is reserved for ${document.getElementById('bookingDate').value}. You can settle payment later.`;
                    showStep(bookingDoneStep);
                } catch (error) {
                    bookingDoneCopy.textContent = 'We could not reserve the room. Check your connection and try again.';
                    showStep(bookingDoneStep);
                } finally {
                    reserveButton.disabled = false;
                }
            });

            function openBookingModal(room) {
                selectedRoom = room;
                selectedMethod = null;
                bookingRoomName.textContent = room.name;
                bookingForm.reset();
                bookingDuration.value = '3';
                updateBookingPrice();
                bookingReferenceNumber.value = '';
                document.getElementById('bookingReceipt').value = '';
                document.getElementById('bookingReceiptName').textContent = 'Choose a screenshot of your receipt (JPG or PNG)';
                showStep(bookingFormStep);
                bookingModal.classList.add('is-open');
                bookingModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('no-scroll');
            }

            document.querySelectorAll('.explore-btn').forEach((button) => {
                button.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const room = JSON.parse(button.dataset.room);
                    modalImage.src = room.image;
                    modalImage.alt = room.name;
                    modalTier.textContent = ({ signature: 'Level 1', comfort: 'Level 2', essential: 'Level 3' })[room.tier] || room.tier;
                    modalTier.className = `tier-badge tier-badge--${room.tier}`;
                    modalTitle.textContent = room.name;
                    modalBlurb.textContent = room.blurb;
                    modalCapacity.textContent = `Up to ${room.max_guests} guests`;
                    modalPrice.textContent = `₱${room.price}`;
                    modalTags.replaceChildren(...room.tags.map((tag) => {
                        const item = document.createElement('li');
                        item.textContent = tag;
                        return item;
                    }));
                    selectedRoom = room;
                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    document.body.classList.add('no-scroll');
                });
            });
            document.getElementById('exploreModalClose')?.addEventListener('click', closeModal);
            document.getElementById('exploreModalBackdrop')?.addEventListener('click', closeModal);
            document.getElementById('exploreModalBook')?.addEventListener('click', () => {
                closeModal();
                openBookingModal(selectedRoom);
            });
            document.getElementById('bookingModalClose')?.addEventListener('click', closeBookingModal);
            document.getElementById('bookingModalBackdrop')?.addEventListener('click', closeBookingModal);
            document.getElementById('bookingDoneClose')?.addEventListener('click', closeBookingModal);
            // Step 1 -> Step 2: guest details are filled in, move to payment method
            bookingForm?.addEventListener('submit', (event) => {
                event.preventDefault();
                showStep(bookingPaymentStep);
            });
            document.getElementById('bookingBackToForm')?.addEventListener('click', () => showStep(bookingFormStep));

            // Step 2 -> Step 3: a payment logo was tapped, show its QR
            document.querySelectorAll('.payment-method-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    selectedMethod = button.dataset.method;
                    const label = selectedMethod === 'gcash' ? 'GCash' : 'MariBank';
                    const amount = selectedRoom ? selectedRoom.duration_prices[bookingDuration.value] : 0;

                    bookingQrMethodLabel.textContent = label;
                    bookingQrMethodName.textContent = label;
                    paymentQrAmount.textContent = `₱${amount}`;

                    paymentQrImage.hidden = false;
                    paymentQrMissingNote.hidden = true;

                    if (selectedMethod === 'gcash') {
                        paymentQrImage.src = GCASH_QR_DATA_URI;
                        paymentQrImage.alt = `GCash QR code for ${PAYMENT_NUMBER}`;
                        paymentQrImage.onerror = null;
                    } else {
                        const qrPayload = [
                            'MariBank Payment',
                            `Send to: ${PAYMENT_NUMBER}`,
                            `Amount: PHP ${amount}`,
                            `Room: ${selectedRoom ? selectedRoom.name : ''}`,
                        ].join('\n');

                        paymentQrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=${encodeURIComponent(qrPayload)}`;
                        paymentQrImage.alt = `MariBank QR code for ${PAYMENT_NUMBER}`;
                        paymentQrImage.onerror = null;
                    }

                    showStep(bookingQrStep);
                });
            });
            document.getElementById('bookingBackToPayment')?.addEventListener('click', () => showStep(bookingPaymentStep));

            document.getElementById('bookingReceipt')?.addEventListener('change', (event) => {
                const file = event.target.files[0];
                document.getElementById('bookingReceiptName').textContent =
                    file ? file.name : 'Choose a screenshot of your receipt (JPG or PNG)';
            });

            // Step 3 -> submit the booking (guest info + chosen method + optional reference)
            document.getElementById('bookingConfirmPayment')?.addEventListener('click', async () => {
                const receiptInput = document.getElementById('bookingReceipt');

                if (!receiptInput.files.length) {
                    document.getElementById('bookingReceiptName').textContent = 'Please choose your receipt image first.';
                    return;
                }

                const confirmButton = document.getElementById('bookingConfirmPayment');
                confirmButton.disabled = true;

                const bookingData = new FormData();
                bookingData.append('booking_type', 'pay_now');
                bookingData.append('room_id', selectedRoom.id);
                bookingData.append('guest_name', bookingFormFullname.value);
                bookingData.append('guest_count', String(DEFAULT_GUEST_COUNT));
                bookingData.append('duration_hours', bookingDuration.value);
                bookingData.append('stay_date', document.getElementById('bookingDate').value);
                bookingData.append('contact_number', document.getElementById('bookingContact').value);
                bookingData.append('payment_method', selectedMethod);
                bookingData.append('reference_number', bookingReferenceNumber.value || '');
                bookingData.append('receipt', receiptInput.files[0]);

                let response;
                try {
                    response = await fetch('{{ route('rooms.book') }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: bookingData,
                    });
                } catch (error) {
                    bookingDoneCopy.textContent = 'We could not send your request. Check your connection and try again.';
                    showStep(bookingDoneStep);
                    return;
                } finally {
                    confirmButton.disabled = false;
                }

                if (!response.ok) {
                    bookingDoneCopy.textContent = (await bookingErrorMessage(response))
                        || 'We could not send your request. Please check your details and try again.';
                    showStep(bookingDoneStep);
                    return;
                }

                showStep(bookingSendStep);
                envelope.classList.remove('is-sealed', 'is-dropping');
                void envelope.offsetWidth;
                sendingText.textContent = 'Sealing your request...';
                requestAnimationFrame(() => envelope.classList.add('is-sealed'));
                window.setTimeout(() => {
                    sendingText.textContent = 'Sending your receipt to our front desk...';
                    envelope.classList.add('is-dropping');
                }, 3500);
                window.setTimeout(() => {
                    sendingText.textContent = 'Almost there...';
                }, 7500);
                window.setTimeout(() => {
                    bookingDoneCopy.textContent = `Thanks, ${bookingFormFullname.value}. We received your receipt for ${selectedRoom.name}. Our admin will review it and approve your booking.`;
                    showStep(bookingDoneStep);
                }, 10000);
            });

            const filter = document.getElementById('roomsFilter');
            const grid = document.getElementById('roomsGrid');
            const cards = grid?.querySelectorAll('.room-card') || [];

            function applyRoomFilter(tier) {
                if (!filter) return;

                filter.dataset.active = tier;
                filter.querySelectorAll('.filter-pill').forEach((pill) => {
                    const isActive = pill.dataset.filter === tier;
                    pill.classList.toggle('is-active', isActive);
                    pill.setAttribute('aria-pressed', String(isActive));
                });

                cards.forEach((card) => {
                    const matches = tier === 'all' || card.dataset.tier === tier;
                    card.hidden = !matches;
                    card.classList.toggle('is-hiding', !matches);
                });
            }

            filter?.querySelectorAll('.filter-pill').forEach((pill) => {
                pill.addEventListener('click', () => applyRoomFilter(pill.dataset.filter));
            });
            applyRoomFilter(filter?.dataset.active || 'all');

            const revealObserver = new IntersectionObserver((entries) => entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            }), { threshold: 0.15 });
            cards.forEach((card) => revealObserver.observe(card));

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    closeLightbox();
                    closeModal();
                    closeBookingModal();
                }
            });
        })();
    </script>
</x-page>
