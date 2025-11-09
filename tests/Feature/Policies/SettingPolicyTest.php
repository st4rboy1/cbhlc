<?php

use App\Models\Setting;
use App\Models\User;
use App\Policies\SettingPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new SettingPolicy;
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('allows super_admin to view any settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('allows administrator to view any settings', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('denies other roles from viewing any settings', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');

    expect($this->policy->viewAny($user))->toBeFalse();
});

it('allows super_admin to view a setting', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $setting = Setting::factory()->create();

    expect($this->policy->view($user, $setting))->toBeTrue();
});

it('allows administrator to view a setting', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $setting = Setting::factory()->create();

    expect($this->policy->view($user, $setting))->toBeTrue();
});

it('denies other roles from viewing a setting', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');
    $setting = Setting::factory()->create();

    expect($this->policy->view($user, $setting))->toBeFalse();
});

it('allows super_admin to create settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->create($user))->toBeTrue();
});

it('allows administrator to create settings', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    expect($this->policy->create($user))->toBeTrue();
});

it('denies other roles from creating settings', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');

    expect($this->policy->create($user))->toBeFalse();
});

it('allows super_admin to update a setting', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $setting = Setting::factory()->create();

    expect($this->policy->update($user, $setting))->toBeTrue();
});

it('allows administrator to update a setting', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $setting = Setting::factory()->create();

    expect($this->policy->update($user, $setting))->toBeTrue();
});

it('denies other roles from updating a setting', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');
    $setting = Setting::factory()->create();

    expect($this->policy->update($user, $setting))->toBeFalse();
});

it('allows only super_admin to delete settings', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $setting = Setting::factory()->create();

    expect($this->policy->delete($user, $setting))->toBeTrue();
});

it('denies administrator from deleting settings', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $setting = Setting::factory()->create();

    expect($this->policy->delete($user, $setting))->toBeFalse();
});

it('denies other roles from deleting settings', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');
    $setting = Setting::factory()->create();

    expect($this->policy->delete($user, $setting))->toBeFalse();
});
