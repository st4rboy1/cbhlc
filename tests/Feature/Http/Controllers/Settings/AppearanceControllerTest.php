<?php

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->user = User::factory()->create();
});

test('appearance settings page requires authentication', function () {
    $response = $this->get('/settings/appearance');

    $response->assertRedirect('/login');
});

test('authenticated user can view appearance settings', function () {
    $response = $this->actingAs($this->user)->get('/settings/appearance');

    $response->assertOk();
    $response->assertInertia(fn (AssertableInertia $page) => $page
        ->component('settings/appearance')
    );
});

test('user can update appearance settings', function () {
    $response = $this->actingAs($this->user)->post('/settings/appearance', [
        'appearance' => 'dark',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('appearance', 'dark');
    $response->assertSessionHas('success', 'Appearance settings updated successfully.');
});

test('appearance settings persist in session', function () {
    $this->withSession(['appearance' => 'light'])
        ->actingAs($this->user)
        ->post('/settings/appearance', [
            'appearance' => 'light',
        ]);

    $response = $this->get('/settings/appearance');

    $response->assertSessionHas('appearance', 'light');
});
