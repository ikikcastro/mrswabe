<?php

use App\Models\User;
use App\Models\LoginActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('owner-created customer accounts record the owner who created them', function () {
    $owner = User::factory()->create();
    $owner->assignRole(Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']));

    $this->actingAs($owner)
        ->post(route('admin.accounts.store'), [
            'name' => 'Created Customer',
            'email' => 'customer@example.com',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ])
        ->assertRedirect(route('admin.dashboard').'#accounts')
        ->assertSessionHas('status', 'Customer account created.');

    $customer = User::where('email', 'customer@example.com')->firstOrFail();

    expect($customer->registered_by_user_id)->toBe($owner->id)
        ->and($customer->registeredBy->is($owner))->toBeTrue()
        ->and($customer->hasRole('customer'))->toBeTrue();
});

test('customer accounts list the account creator in the admin dashboard', function () {
    $owner = User::factory()->create(['name' => 'Account Owner']);
    $owner->assignRole(Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']));
    $customer = User::factory()->create([
        'name' => 'Created Customer',
        'registered_by_user_id' => $owner->id,
    ]);

    $this->actingAs($owner)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Created Customer')
        ->assertSee('Account Owner');
});

test('the dashboard marks public registrations as self-registered', function () {
    $owner = User::factory()->create();
    $owner->assignRole(Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']));

    $this->post('/register', [
        'name' => 'Self Registered',
        'email' => 'self@example.com',
        'password' => 'secure-password-123',
    ]);
    $customer = User::where('email', 'self@example.com')->firstOrFail();

    $this->actingAs($owner)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Self Registered (self-registered)');

    expect($customer->registered_by_user_id)->toBe($customer->id);
});

test('non-owner accounts cannot create customer accounts', function () {
    $customer = User::factory()->create();
    $customer->assignRole(Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']));

    $this->actingAs($customer)
        ->post(route('admin.accounts.store'), [
            'name' => 'Unauthorized Customer',
            'email' => 'unauthorized@example.com',
            'password' => 'secure-password-123',
            'password_confirmation' => 'secure-password-123',
        ])
        ->assertForbidden();

    $this->assertDatabaseMissing('users', ['email' => 'unauthorized@example.com']);
});

test('successful owner login is recorded as login activity', function () {
    $owner = User::factory()->create([
        'username' => 'test-owner',
        'password' => 'secure-password-123',
    ]);
    $owner->assignRole(Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']));

    $this->post(route('admin.login.attempt'), [
        'username' => 'test-owner',
        'password' => 'secure-password-123',
    ])->assertRedirect('/admin/dashboard');

    $this->assertDatabaseHas('login_activities', [
        'user_id' => $owner->id,
        'login_type' => 'owner',
    ]);
});
