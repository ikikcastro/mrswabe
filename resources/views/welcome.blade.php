<x-page>
    @slot('title')
        ikik House | Welcome
    @endslot

    @slot('bodyClass')
    @endslot

     @auth
    <main class="account-page">
        <section class="signed-in-panel">
            <p class="eyebrow">ikik House</p>
            <h1>Your stay starts here.</h1>
            <p class="panel-copy">You are signed in and ready to plan your next escape.</p>
            <form class="logout-form" action="/logout" method="POST">
                @csrf
                <button class="button button-dark" type="submit">Logout</button>
            </form>
        </section>
    </main>
    @else
    <main class="welcome-page">
        <section class="welcome-splash" id="welcomeSplash" aria-label="Welcome animation, tap to continue">
            <svg class="house-build" viewBox="0 0 400 320" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="A house assembling">
                <ellipse class="house-ground" cx="200" cy="284" rx="130" ry="8"></ellipse>
                <g class="house-piece piece-wall">
                    <rect x="120" y="180" width="160" height="100" rx="2"></rect>
                </g>
                <g class="house-piece piece-window-left">
                    <rect x="140" y="196" width="26" height="26" rx="1"></rect>
                    <line x1="153" y1="196" x2="153" y2="222"></line>
                    <line x1="140" y1="209" x2="166" y2="209"></line>
                </g>
                <g class="house-piece piece-window-right">
                    <rect x="234" y="196" width="26" height="26" rx="1"></rect>
                    <line x1="247" y1="196" x2="247" y2="222"></line>
                    <line x1="234" y1="209" x2="260" y2="209"></line>
                </g>
                <g class="house-piece piece-door">
                    <rect x="182" y="222" width="36" height="58" rx="1"></rect>
                    <circle class="door-knob" cx="211" cy="252" r="1.6"></circle>
                </g>
                <g class="house-piece piece-roof">
                    <polygon points="108,180 200,104 292,180"></polygon>
                </g>
                <g class="house-piece piece-chimney">
                    <rect x="246" y="120" width="18" height="42"></rect>
                </g>
            </svg>
        </section>

        <div class="welcome-main" id="welcomeMain">
            <section class="welcome-intro">
                <div class="brand-mark" aria-hidden="true">HH</div>
                <p class="eyebrow">A quieter kind of stay</p>
                <h1>Make room for the good days.</h1>
                <p class="intro-copy">Join ikik House for thoughtful rooms, unhurried mornings, and a place that feels like yours.</p>
                <div class="intro-detail"><span class="detail-line"></span><span>Stay beautifully</span></div>
            </section>

            <section class="auth-area" aria-label="Account access">
                <div class="auth-heading">
                    <p class="eyebrow">Welcome in</p>
                </div>

            <!-- Toggle switch -->
            <div class="auth-switch" id="authSwitch" data-active="{{ session()->has('registration_success') ? 'login' : 'register' }}">
                <div class="auth-switch-indicator"></div>
                <button type="button" id="switchRegister" class="{{ session()->has('registration_success') ? '' : 'active' }}">Register</button>
                <button type="button" id="switchLogin" class="{{ session()->has('registration_success') ? 'active' : '' }}">Login</button>
            </div>

                @if (session('registration_success'))
                    <p class="auth-feedback auth-feedback--success" role="status">{{ session('registration_success') }}</p>
                @endif
                @if ($errors->any())
                    <div class="auth-feedback auth-feedback--error" role="alert">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="auth-grid" id="authGrid" data-active="{{ session()->has('registration_success') || $errors->has('email') ? 'login' : 'register' }}">
                <div class="auth-card register-card">
                    <span class="card-number">01</span>
                    <h3>Create an account</h3>
                    <p class="card-copy">Save your details and make every booking feel effortless.</p>
                    <form class="auth-form" action="/register" method="POST">
                        @csrf
                        <label for="register-name">Full name</label>
                        <input id="register-name" name="name" type="text" placeholder="Your name" autocomplete="name" value="{{ old('name') }}" required>
                        <label for="register-email">Email address</label>
                        <input id="register-email" name="email" type="email" placeholder="you@example.com" autocomplete="email" value="{{ old('email') }}" required>
                        <label for="register-password">Password</label>
                        <div class="password-field">
                            <input id="register-password" name="password" type="password" placeholder="Create a password" autocomplete="new-password" required>
                            <button class="password-toggle" type="button" data-password-toggle aria-controls="register-password" aria-label="Show password" aria-pressed="false">Show</button>
                        </div>
                        <button class="button button-light" type="submit">Register <span aria-hidden="true">&#8594;</span></button>
                    </form>
                    <button type="button" class="auth-swap-link" data-swap="login">Already registered? Log in instead</button>
                </div>

                <div class="auth-card login-card">
                    <span class="card-number">02</span>
                    <h3>Welcome back</h3>
                    <p class="card-copy">Already have a room here? Pick up right where you left off.</p>
                    <form class="auth-form" action="/login" method="POST">
                        @csrf
                        <label for="login-email">Email address</label>
                        <input id="login-email" name="email" type="email" placeholder="you@example.com" autocomplete="email" value="{{ old('email', session('registered_email')) }}" required>
                        <label for="login-password">Password</label>
                        <div class="password-field">
                            <input id="login-password" name="password" type="password" placeholder="Your password" autocomplete="current-password" required>
                            <button class="password-toggle" type="button" data-password-toggle aria-controls="login-password" aria-label="Show password" aria-pressed="false">Show</button>
                        </div>
                        <button class="button button-dark" type="submit">Login <span aria-hidden="true">&#8594;</span></button>
                    </form>
                    <button type="button" class="auth-swap-link" data-swap="register">New here? Create an account</button>
                </div>
                </div>
            </section>
        </div>
    </main>
    @endauth

    <script>
        (() => {
            const switchElement = document.getElementById('authSwitch');
            const gridElement = document.getElementById('authGrid');
            const registerButton = document.getElementById('switchRegister');
            const loginButton = document.getElementById('switchLogin');

            if (!switchElement || !gridElement || !registerButton || !loginButton) {
                return;
            }

            const swapLinks = document.querySelectorAll('[data-swap]');

            function setActive(mode) {
                const isLogin = mode === 'login';

                switchElement.dataset.active = mode;
                gridElement.dataset.active = mode;
                registerButton.classList.toggle('active', !isLogin);
                loginButton.classList.toggle('active', isLogin);
            }

            registerButton.addEventListener('click', () => setActive('register'));
            loginButton.addEventListener('click', () => setActive('login'));
            swapLinks.forEach((link) => {
                link.addEventListener('click', () => setActive(link.dataset.swap));
            });

            if (gridElement.dataset.active === 'login') {
                setActive('login');
            }
        })();

        (() => {
            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.getAttribute('aria-controls'));
                    const isVisible = input.type === 'text';

                    input.type = isVisible ? 'password' : 'text';
                    button.textContent = isVisible ? 'Show' : 'Hide';
                    button.setAttribute('aria-label', `${isVisible ? 'Show' : 'Hide'} password`);
                    button.setAttribute('aria-pressed', String(!isVisible));
                });
            });
        })();

        (() => {
            const splash = document.getElementById('welcomeSplash');
            const main = document.getElementById('welcomeMain');

            if (!splash || !main) {
                return;
            }

            let finished = false;
            let autoTimer;

            function reveal() {
                if (finished) {
                    return;
                }

                finished = true;
                window.clearTimeout(autoTimer);
                splash.classList.add('is-leaving');
                window.setTimeout(() => {
                    splash.style.display = 'none';
                    main.classList.add('is-entering');
                }, 500);
            }

            autoTimer = window.setTimeout(reveal, 3400);
            splash.addEventListener('click', reveal);
        })();
    </script>
</x-page>