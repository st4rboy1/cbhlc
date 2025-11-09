<?php

use App\Models\Receipt;
use App\Models\User;
use App\Policies\ReceiptPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->policy = new ReceiptPolicy;
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('allows super_admin to view any receipts', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('allows administrator to view any receipts', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('allows registrar to view any receipts', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('allows guardian to view any receipts', function () {
    $user = User::factory()->create();
    $user->assignRole('guardian');

    expect($this->policy->viewAny($user))->toBeTrue();
});

it('allows super_admin to view a receipt', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $receipt = Receipt::factory()->create();

    expect($this->policy->view($user, $receipt))->toBeTrue();
});

it('allows administrator to view a receipt', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $receipt = Receipt::factory()->create();

    expect($this->policy->view($user, $receipt))->toBeTrue();
});

it('allows super_admin to create receipts', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($this->policy->create($user))->toBeTrue();
});

it('allows administrator to create receipts', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');

    expect($this->policy->create($user))->toBeTrue();
});

it('allows registrar to create receipts', function () {
    $user = User::factory()->create();
    $user->assignRole('registrar');

    expect($this->policy->create($user))->toBeTrue();
});

it('allows super_admin to update a receipt', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $receipt = Receipt::factory()->create();

    expect($this->policy->update($user, $receipt))->toBeTrue();
});

it('allows administrator to update a receipt', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $receipt = Receipt::factory()->create();

    expect($this->policy->update($user, $receipt))->toBeTrue();
});

it('allows only super_admin to delete a receipt', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $receipt = Receipt::factory()->create();

    expect($this->policy->delete($user, $receipt))->toBeTrue();
});

it('denies administrator from deleting a receipt', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $receipt = Receipt::factory()->create();

    expect($this->policy->delete($user, $receipt))->toBeFalse();
});

it('allows only super_admin to restore a receipt', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $receipt = Receipt::factory()->create();

    expect($this->policy->restore($user, $receipt))->toBeTrue();
});

it('denies administrator from restoring a receipt', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $receipt = Receipt::factory()->create();

    expect($this->policy->restore($user, $receipt))->toBeFalse();
});

it('allows only super_admin to force delete a receipt', function () {
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $receipt = Receipt::factory()->create();

    expect($this->policy->forceDelete($user, $receipt))->toBeTrue();
});

it('denies administrator from force deleting a receipt', function () {
    $user = User::factory()->create();
    $user->assignRole('administrator');
    $receipt = Receipt::factory()->create();

    expect($this->policy->forceDelete($user, $receipt))->toBeFalse();
});
