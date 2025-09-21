<?php

use App\Enums\RelationshipType;

test('relationship type enum has correct values', function () {
    expect(RelationshipType::FATHER->value)->toBe('father');
    expect(RelationshipType::MOTHER->value)->toBe('mother');
    expect(RelationshipType::GUARDIAN->value)->toBe('guardian');
    expect(RelationshipType::GRANDPARENT->value)->toBe('grandparent');
    expect(RelationshipType::OTHER->value)->toBe('other');
});

test('relationship type enum labels are correct', function () {
    expect(RelationshipType::FATHER->label())->toBe('Father');
    expect(RelationshipType::MOTHER->label())->toBe('Mother');
    expect(RelationshipType::GUARDIAN->label())->toBe('Guardian');
    expect(RelationshipType::GRANDPARENT->label())->toBe('Grandparent');
    expect(RelationshipType::OTHER->label())->toBe('Other');
});

test('relationship type values method returns correct array', function () {
    $values = RelationshipType::values();

    expect($values)->toContain('father');
    expect($values)->toContain('mother');
    expect($values)->toContain('guardian');
    expect($values)->toContain('grandparent');
    expect($values)->toContain('other');
    expect($values)->toHaveCount(5);
});

test('relationship type options method returns correct format', function () {
    $options = RelationshipType::options();

    expect($options)->toBeArray();
    expect($options)->toHaveCount(5);

    $expectedOption = [
        'value' => 'father',
        'label' => 'Father',
    ];

    expect($options[0])->toBe($expectedOption);
});

test('relationship type enum can be created from string values', function () {
    expect(RelationshipType::from('father'))->toBe(RelationshipType::FATHER);
    expect(RelationshipType::from('mother'))->toBe(RelationshipType::MOTHER);
    expect(RelationshipType::from('guardian'))->toBe(RelationshipType::GUARDIAN);
    expect(RelationshipType::from('grandparent'))->toBe(RelationshipType::GRANDPARENT);
    expect(RelationshipType::from('other'))->toBe(RelationshipType::OTHER);
});

test('relationship type enum throws exception for invalid values', function () {
    RelationshipType::from('invalid');
})->throws(ValueError::class);
