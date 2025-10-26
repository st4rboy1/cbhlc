<?php

use App\Models\SchoolYear;
use App\Models\User;
use App\Policies\SchoolYearPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new SchoolYearPolicy;
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('allows super_admin to view any school years', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('allows administrator to view any school years', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('allows registrar to view any school years', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('denies other roles from viewing any school years', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    expect($this->policy->viewAny($user))->toBeFalse();
});

it('allows super_admin to view a school year', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $schoolYear = SchoolYear::factory()->create();

    expect($this->policy->view($user, $schoolYear))->toBeTrue();
});

it('allows administrator to view a school year', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $schoolYear = SchoolYear::factory()->create();

    expect($this->policy->view($user, $schoolYear))->toBeTrue();
});

it('allows registrar to view a school year', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');
    $schoolYear = SchoolYear::factory()->create();

    expect($this->policy->view($user, $schoolYear))->toBeTrue();
});

it('allows super_admin to create school years', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->create($user))->toBeTrue();
});

it('allows administrator to create school years', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    expect($this->policy->create($user))->toBeTrue();
});

it('allows super_admin to update a school year', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $schoolYear = SchoolYear::factory()->create();

    expect($this->policy->update($user, $schoolYear))->toBeTrue();
});

it('allows administrator to update a school year', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $schoolYear = SchoolYear::factory()->create();

    expect($this->policy->update($user, $schoolYear))->toBeTrue();
});

it('allows only super_admin to delete a school year', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $schoolYear = SchoolYear::factory()->create();

    expect($this->policy->delete($user, $schoolYear))->toBeTrue();
});

it('denies administrator from deleting a school year', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $schoolYear = SchoolYear::factory()->create();

    expect($this->policy->delete($user, $schoolYear))->toBeFalse();
});

it('allows only super_admin to restore a school year', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $schoolYear = SchoolYear::factory()->create();

    expect($this->policy->restore($user, $schoolYear))->toBeTrue();
});

it('denies administrator from restoring a school year', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $schoolYear = SchoolYear::factory()->create();

    expect($this->policy->restore($user, $schoolYear))->toBeFalse();
});

it('allows only super_admin to force delete a school year', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $schoolYear = SchoolYear::factory()->create();

    expect($this->policy->forceDelete($user, $schoolYear))->toBeTrue();
});

it('denies administrator from force deleting a school year', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $schoolYear = SchoolYear::factory()->create();

    expect($this->policy->forceDelete($user, $schoolYear))->toBeFalse();
});
