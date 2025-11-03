<?php

use App\Models\Setting;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    // Clear cache before each test
    Cache::flush();
});

test('can create a setting', function () {
    $setting = Setting::create([
        'key' => 'test_key',
        'value' => 'test_value',
        'type' => 'string',
        'description' => 'Test setting',
        'is_public' => true,
    ]);

    expect($setting)->toBeInstanceOf(Setting::class)
        ->and($setting->key)->toBe('test_key')
        ->and($setting->value)->toBe('test_value');
});

test('can get a setting value', function () {
    Setting::create([
        'key' => 'test_key',
        'value' => 'test_value',
        'type' => 'string',
    ]);

    $value = Setting::get('test_key');

    expect($value)->toBe('test_value');
});

test('get returns default when setting does not exist', function () {
    $value = Setting::get('nonexistent', 'default_value');

    expect($value)->toBe('default_value');
});

test('can set a setting value', function () {
    Setting::set('new_key', 'new_value', 'string', 'New setting', true);

    $setting = Setting::where('key', 'new_key')->first();

    expect($setting)->not->toBeNull()
        ->and($setting->value)->toBe('new_value')
        ->and($setting->type)->toBe('string')
        ->and($setting->description)->toBe('New setting')
        ->and($setting->is_public)->toBeTrue();
});

test('set updates existing setting', function () {
    Setting::create([
        'key' => 'existing_key',
        'value' => 'old_value',
        'type' => 'string',
    ]);

    Setting::set('existing_key', 'new_value');

    $setting = Setting::where('key', 'existing_key')->first();

    expect($setting->value)->toBe('new_value');
});

test('has returns true for existing setting', function () {
    Setting::create([
        'key' => 'existing_key',
        'value' => 'value',
        'type' => 'string',
    ]);

    expect(Setting::has('existing_key'))->toBeTrue();
});

test('has returns false for non-existing setting', function () {
    expect(Setting::has('nonexistent'))->toBeFalse();
});

test('can forget a setting', function () {
    Setting::create([
        'key' => 'to_delete',
        'value' => 'value',
        'type' => 'string',
    ]);

    $result = Setting::forget('to_delete');

    expect($result)->toBeTrue()
        ->and(Setting::where('key', 'to_delete')->exists())->toBeFalse();
});

test('forget returns false when setting does not exist', function () {
    $result = Setting::forget('nonexistent');

    expect($result)->toBeFalse();
});

test('can get public settings only', function () {
    Setting::create(['key' => 'public1', 'value' => 'value1', 'type' => 'string', 'is_public' => true]);
    Setting::create(['key' => 'public2', 'value' => 'value2', 'type' => 'string', 'is_public' => true]);
    Setting::create(['key' => 'private', 'value' => 'value3', 'type' => 'string', 'is_public' => false]);

    $publicSettings = Setting::getPublic();

    expect($publicSettings)->toHaveCount(2)
        ->and($publicSettings->has('public1'))->toBeTrue()
        ->and($publicSettings->has('public2'))->toBeTrue()
        ->and($publicSettings->has('private'))->toBeFalse();
});

test('casts integer values correctly', function () {
    Setting::create(['key' => 'int_value', 'value' => '42', 'type' => 'integer']);

    $value = Setting::get('int_value');

    expect($value)->toBe(42)
        ->and($value)->toBeInt();
});

test('casts boolean values correctly', function () {
    Setting::create(['key' => 'bool_true', 'value' => '1', 'type' => 'boolean']);
    Setting::create(['key' => 'bool_false', 'value' => '0', 'type' => 'boolean']);

    expect(Setting::get('bool_true'))->toBeTrue()
        ->and(Setting::get('bool_false'))->toBeFalse();
});

test('casts float values correctly', function () {
    Setting::create(['key' => 'float_value', 'value' => '3.14', 'type' => 'float']);

    $value = Setting::get('float_value');

    expect($value)->toBe(3.14)
        ->and($value)->toBeFloat();
});

test('casts array values correctly', function () {
    Setting::create([
        'key' => 'array_value',
        'value' => json_encode(['key1' => 'value1', 'key2' => 'value2']),
        'type' => 'array',
    ]);

    $value = Setting::get('array_value');

    expect($value)->toBeArray()
        ->and($value)->toHaveKey('key1')
        ->and($value['key1'])->toBe('value1');
});

test('set infers type from value', function () {
    Setting::set('string_val', 'text');
    Setting::set('int_val', 42);
    Setting::set('bool_val', true);
    Setting::set('float_val', 3.14);
    Setting::set('array_val', ['a' => 'b']);

    expect(Setting::where('key', 'string_val')->first()->type)->toBe('string')
        ->and(Setting::where('key', 'int_val')->first()->type)->toBe('integer')
        ->and(Setting::where('key', 'bool_val')->first()->type)->toBe('boolean')
        ->and(Setting::where('key', 'float_val')->first()->type)->toBe('float')
        ->and(Setting::where('key', 'array_val')->first()->type)->toBe('array');
});

test('settings are cached', function () {
    Setting::create(['key' => 'cached_key', 'value' => 'cached_value', 'type' => 'string']);

    // First call should query database
    $value1 = Setting::get('cached_key');

    // Second call should use cache
    $value2 = Setting::get('cached_key');

    expect($value1)->toBe('cached_value')
        ->and($value2)->toBe('cached_value')
        ->and(Cache::has(Setting::CACHE_KEY))->toBeTrue();
});

test('cache is cleared when setting is saved', function () {
    Setting::create(['key' => 'test_key', 'value' => 'old_value', 'type' => 'string']);

    // Trigger cache
    Setting::get('test_key');
    expect(Cache::has(Setting::CACHE_KEY))->toBeTrue();

    // Update setting
    $setting = Setting::where('key', 'test_key')->first();
    $setting->update(['value' => 'new_value']);

    // Cache should be cleared
    expect(Cache::has(Setting::CACHE_KEY))->toBeFalse();
});

test('cache is cleared when setting is deleted', function () {
    Setting::create(['key' => 'test_key', 'value' => 'value', 'type' => 'string']);

    // Trigger cache
    Setting::get('test_key');
    expect(Cache::has(Setting::CACHE_KEY))->toBeTrue();

    // Delete setting
    Setting::forget('test_key');

    // Cache should be cleared
    expect(Cache::has(Setting::CACHE_KEY))->toBeFalse();
});

test('clearCache clears both setting caches', function () {
    Setting::create(['key' => 'test_key', 'value' => 'value', 'type' => 'string', 'is_public' => true]);

    // Trigger both caches
    Setting::get('test_key');
    Setting::getPublic();

    expect(Cache::has(Setting::CACHE_KEY))->toBeTrue()
        ->and(Cache::has(Setting::CACHE_KEY.'_public'))->toBeTrue();

    // Clear caches
    Setting::clearCache();

    expect(Cache::has(Setting::CACHE_KEY))->toBeFalse()
        ->and(Cache::has(Setting::CACHE_KEY.'_public'))->toBeFalse();
});

test('helper function setting() works', function () {
    Setting::create(['key' => 'helper_test', 'value' => 'helper_value', 'type' => 'string']);

    expect(setting('helper_test'))->toBe('helper_value')
        ->and(setting('nonexistent', 'default'))->toBe('default');
});

test('helper function setting_set() works', function () {
    setting_set('helper_key', 'helper_value', 'string', 'Helper description', true);

    $setting = Setting::where('key', 'helper_key')->first();

    expect($setting)->not->toBeNull()
        ->and($setting->value)->toBe('helper_value')
        ->and($setting->description)->toBe('Helper description')
        ->and($setting->is_public)->toBeTrue();
});

test('helper function setting_has() works', function () {
    Setting::create(['key' => 'exists', 'value' => 'value', 'type' => 'string']);

    expect(setting_has('exists'))->toBeTrue()
        ->and(setting_has('not_exists'))->toBeFalse();
});

test('helper function setting_forget() works', function () {
    Setting::create(['key' => 'to_forget', 'value' => 'value', 'type' => 'string']);

    expect(setting_forget('to_forget'))->toBeTrue()
        ->and(Setting::where('key', 'to_forget')->exists())->toBeFalse();
});

test('helper function public_settings() works', function () {
    Setting::create(['key' => 'public', 'value' => 'value', 'type' => 'string', 'is_public' => true]);
    Setting::create(['key' => 'private', 'value' => 'value', 'type' => 'string', 'is_public' => false]);

    $publicSettings = public_settings();

    expect($publicSettings)->toHaveCount(1)
        ->and($publicSettings->has('public'))->toBeTrue()
        ->and($publicSettings->has('private'))->toBeFalse();
});
