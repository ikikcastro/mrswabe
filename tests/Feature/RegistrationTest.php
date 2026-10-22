<?php

use App\Models\User;
use App\Models\LoginActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('registration stores the user and assigns the customer role', function () {
    $response = $this->post('/register', [
        'name' => 'Taylor Guest',
        'email' => 'taylor@example.com',
        'password' => 'secure-password-123',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('registration_success');
    $response->assertSessionHas('registered_email', 'taylor@example.com');

    $user = User::where('email', 'taylor@example.com')->firstOrFail();

    expect($user->name)->toBe('Taylor Guest')
        ->and(Hash::check('secure-password-123', $user->password))->toBeTrue()
        ->and($user->registered_by_user_id)->toBe($user->id)
        ->and($user->registeredBy->is($user))->toBeTrue()
        ->and($user->hasRole('customer'))->toBeTrue();

    $this->assertGuest();

    $loginResponse = $this->post('/login', [
        'email' => 'taylor@example.com',
        'password' => 'secure-password-123',
    ]);

    $loginResponse->assertRedirect('/rooms');
    $this->assertAuthenticatedAs($user);

    expect(LoginActivity::where('user_id', $user->id)->count())->toBe(1)
        ->and(LoginActivity::where('user_id', $user->id)->first()->login_type)->toBe('customer');
});