<?php

use App\Enums\Quarter;

test('quarter enum has correct values', function () {
    expect(Quarter::FIRST->value)->toBe('First');
    expect(Quarter::SECOND->value)->toBe('Second');
    expect(Quarter::THIRD->value)->toBe('Third');
    expect(Quarter::FOURTH->value)->toBe('Fourth');
});

test('quarter enum labels are correct', function () {
    expect(Quarter::FIRST->label())->toBe('1st Quarter');
    expect(Quarter::SECOND->label())->toBe('2nd Quarter');
    expect(Quarter::THIRD->label())->toBe('3rd Quarter');
    expect(Quarter::FOURTH->label())->toBe('4th Quarter');
});

test('quarter values method returns correct array', function () {
    $values = Quarter::values();

    expect($values)->toContain('First');
    expect($values)->toContain('Second');
    expect($values)->toContain('Third');
    expect($values)->toContain('Fourth');
    expect($values)->toHaveCount(4);
});

test('quarter enum can be created from string values', function () {
    expect(Quarter::from('First'))->toBe(Quarter::FIRST);
    expect(Quarter::from('Second'))->toBe(Quarter::SECOND);
    expect(Quarter::from('Third'))->toBe(Quarter::THIRD);
    expect(Quarter::from('Fourth'))->toBe(Quarter::FOURTH);
});

test('quarter enum throws exception for invalid values', function () {
    Quarter::from('invalid');
})->throws(ValueError::class);