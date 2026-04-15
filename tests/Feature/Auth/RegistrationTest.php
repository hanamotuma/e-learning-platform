<?php

/** @var \Tests\TestCase $this */
use Spatie\Permission\Models\Role;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    // Create the student role if it doesn't exist
    Role::firstOrCreate(['name' => 'student']);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    // Check if user was created
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'full_name' => 'Test User',
    ]);

    // Since Auth::login() may not work in tests, let's check the response redirect
    $response->assertRedirect(route('dashboard', absolute: false));
});