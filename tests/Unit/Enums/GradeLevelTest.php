<?php

use App\Enums\GradeLevel;

test('grade level enum has correct values', function () {
    expect(GradeLevel::KINDER->value)->toBe('Kinder');
    expect(GradeLevel::GRADE_1->value)->toBe('Grade 1');
    expect(GradeLevel::GRADE_2->value)->toBe('Grade 2');
    expect(GradeLevel::GRADE_3->value)->toBe('Grade 3');
    expect(GradeLevel::GRADE_4->value)->toBe('Grade 4');
    expect(GradeLevel::GRADE_5->value)->toBe('Grade 5');
    expect(GradeLevel::GRADE_6->value)->toBe('Grade 6');
});

test('grade level enum labels are correct', function () {
    expect(GradeLevel::KINDER->label())->toBe('Kinder');
    expect(GradeLevel::GRADE_1->label())->toBe('Grade 1');
    expect(GradeLevel::GRADE_2->label())->toBe('Grade 2');
    expect(GradeLevel::GRADE_3->label())->toBe('Grade 3');
    expect(GradeLevel::GRADE_4->label())->toBe('Grade 4');
    expect(GradeLevel::GRADE_5->label())->toBe('Grade 5');
    expect(GradeLevel::GRADE_6->label())->toBe('Grade 6');
});

test('grade level values method returns correct array', function () {
    $values = GradeLevel::values();

    expect($values)->toContain('Kinder');
    expect($values)->toContain('Grade 1');
    expect($values)->toContain('Grade 2');
    expect($values)->toContain('Grade 3');
    expect($values)->toContain('Grade 4');
    expect($values)->toContain('Grade 5');
    expect($values)->toContain('Grade 6');
    expect($values)->toHaveCount(7);
});

test('grade level enum can be created from string values', function () {
    expect(GradeLevel::from('Kinder'))->toBe(GradeLevel::KINDER);
    expect(GradeLevel::from('Grade 1'))->toBe(GradeLevel::GRADE_1);
    expect(GradeLevel::from('Grade 2'))->toBe(GradeLevel::GRADE_2);
    expect(GradeLevel::from('Grade 3'))->toBe(GradeLevel::GRADE_3);
    expect(GradeLevel::from('Grade 4'))->toBe(GradeLevel::GRADE_4);
    expect(GradeLevel::from('Grade 5'))->toBe(GradeLevel::GRADE_5);
    expect(GradeLevel::from('Grade 6'))->toBe(GradeLevel::GRADE_6);
});

test('grade level enum throws exception for invalid values', function () {
    GradeLevel::from('invalid');
})->throws(ValueError::class);