<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ikik House | Owner Sign In</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-login-body">
    <div class="admin-login-card {{ $errors->any() ? 'is-shaking' : '' }}">
        <span class="brand-mark brand-mark--sm" aria-hidden="true">HH</span>
        <p class="eyebrow">ikik House</p>
        <h1>Owner sign in</h1>
        <p class="intro-copy admin-login-copy">This area is restricted to hotel management. Customers should use the regular login.</p>

        @if ($errors->any())
            <p class="admin-login-error">Incorrect username or password.</p>
        @endif
        @if (session('error'))
            <p class="admin-login-error">{{ session('error') }}</p>
        @endif

        <form method="POST" action="{{ route('admin.login') }}" class="auth-form admin-login-form">
            @csrf
            <label for="owner-username">Username</label>
            <input id="owner-username" name="username" type="text" placeholder="Owner username" autocomplete="username" required autofocus>
            <label for="owner-password">Password</label>
            <input id="owner-password" name="password" type="password" placeholder="Password" autocomplete="current-password" required>
            <button class="button button-dark" type="submit">Sign in <span aria-hidden="true">&#8594;</span></button>
        </form>
    </div>
</body>
</html>
