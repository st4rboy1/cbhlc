<?php

use App\Enums\Gender;

test('gender enum has correct values', function () {
    expect(Gender::MALE->value)->toBe('Male');
    expect(Gender::FEMALE->value)->toBe('Female');
    expect(Gender::OTHER->value)->toBe('Other');
});

test('gender enum label method works correctly', function () {
    expect(Gender::MALE->label())->toBe('Male');
    expect(Gender::FEMALE->label())->toBe('Female');
    expect(Gender::OTHER->label())->toBe('Other');
});

test('gender enum values method returns correct array', function () {
    $values = Gender::values();

    expect($values)->toContain('Male');
    expect($values)->toContain('Female');
    expect($values)->toContain('Other');
    expect($values)->toHaveCount(3);
});

test('gender enum can be created from string values', function () {
    expect(Gender::from('Male'))->toBe(Gender::MALE);
    expect(Gender::from('Female'))->toBe(Gender::FEMALE);
    expect(Gender::from('Other'))->toBe(Gender::OTHER);
});

test('gender enum throws exception for invalid values', function () {
    Gender::from('invalid');
})->throws(ValueError::class);
