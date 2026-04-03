<?php

use App\Models\User;

it('can login via api and returns token payload', function () {
    $user = User::factory()->admin()->create([
        'email' => 'admin@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'token_type' => 'Bearer',
        ])
        ->assertJsonStructure([
            'success',
            'user' => ['id', 'name', 'email', 'role'],
            'token',
            'token_type',
        ]);
});

it('can fetch current user via api me endpoint', function () {
    $user = User::factory()->admin()->create();
    $token = $user->createToken('mobile-app')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/me');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
});

it('can logout and revoke current access token', function () {
    $user = User::factory()->admin()->create();
    $token = $user->createToken('mobile-app')->plainTextToken;

    $response = $this
        ->withHeader('Authorization', 'Bearer '.$token)
        ->postJson('/api/logout');

    $response
        ->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);

    expect($user->tokens()->count())->toBe(0);
});
