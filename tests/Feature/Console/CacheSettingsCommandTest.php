<?php

use App\Models\Setting;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    Cache::flush();
});

test('settings:cache command caches all settings', function () {
    Setting::create(['key' => 'setting1', 'value' => 'value1', 'type' => 'string']);
    Setting::create(['key' => 'setting2', 'value' => 'value2', 'type' => 'string']);
    Setting::create(['key' => 'setting3', 'value' => 'value3', 'type' => 'string']);

    $this->artisan('settings:cache')
        ->expectsOutput('Caching system settings...')
        ->assertSuccessful();

    expect(Cache::has(Setting::CACHE_KEY))->toBeTrue()
        ->and(Cache::has(Setting::CACHE_KEY.'_public'))->toBeTrue();
});

test('settings:cache command caches correct number of settings', function () {
    Setting::create(['key' => 'setting1', 'value' => 'value1', 'type' => 'string']);
    Setting::create(['key' => 'setting2', 'value' => 'value2', 'type' => 'string']);

    $this->artisan('settings:cache')
        ->expectsOutputToContain('Cached 2 settings successfully')
        ->assertSuccessful();
});

test('settings:cache command with --clear option clears cache', function () {
    Setting::create(['key' => 'setting1', 'value' => 'value1', 'type' => 'string']);

    // First cache the settings
    $this->artisan('settings:cache');
    expect(Cache::has(Setting::CACHE_KEY))->toBeTrue();

    // Then clear the cache
    $this->artisan('settings:cache', ['--clear' => true])
        ->expectsOutput('Clearing settings cache...')
        ->expectsOutputToContain('Settings cache cleared successfully')
        ->assertSuccessful();

    expect(Cache::has(Setting::CACHE_KEY))->toBeFalse()
        ->and(Cache::has(Setting::CACHE_KEY.'_public'))->toBeFalse();
});

test('settings:cache command caches public settings separately', function () {
    Setting::create(['key' => 'public1', 'value' => 'value1', 'type' => 'string', 'is_public' => true]);
    Setting::create(['key' => 'public2', 'value' => 'value2', 'type' => 'string', 'is_public' => true]);
    Setting::create(['key' => 'private', 'value' => 'value3', 'type' => 'string', 'is_public' => false]);

    $this->artisan('settings:cache')
        ->assertSuccessful();

    $publicSettings = Cache::get(Setting::CACHE_KEY.'_public');

    expect($publicSettings)->toHaveCount(2)
        ->and($publicSettings->has('public1'))->toBeTrue()
        ->and($publicSettings->has('public2'))->toBeTrue()
        ->and($publicSettings->has('private'))->toBeFalse();
});

test('settings:cache command works with no settings', function () {
    $this->artisan('settings:cache')
        ->expectsOutputToContain('Cached 0 settings successfully')
        ->assertSuccessful();
});

test('settings:cache command clears old cache before caching', function () {
    Setting::create(['key' => 'setting1', 'value' => 'old_value', 'type' => 'string']);

    // Cache first time
    $this->artisan('settings:cache');
    $firstCache = Cache::get(Setting::CACHE_KEY);

    // Update setting
    Setting::where('key', 'setting1')->update(['value' => 'new_value']);

    // Cache again
    $this->artisan('settings:cache');
    $secondCache = Cache::get(Setting::CACHE_KEY);

    expect($firstCache->get('setting1')->value)->toBe('old_value')
        ->and($secondCache->get('setting1')->value)->toBe('new_value');
});

test('cached settings can be retrieved with Setting::get()', function () {
    Setting::create(['key' => 'test_key', 'value' => 'test_value', 'type' => 'string']);

    $this->artisan('settings:cache');

    $value = Setting::get('test_key');

    expect($value)->toBe('test_value');
});

test('cached settings maintain correct types', function () {
    Setting::create(['key' => 'int_val', 'value' => '42', 'type' => 'integer']);
    Setting::create(['key' => 'bool_val', 'value' => '1', 'type' => 'boolean']);
    Setting::create(['key' => 'str_val', 'value' => 'text', 'type' => 'string']);

    $this->artisan('settings:cache');

    expect(Setting::get('int_val'))->toBe(42)
        ->and(Setting::get('bool_val'))->toBeTrue()
        ->and(Setting::get('str_val'))->toBe('text');
});
