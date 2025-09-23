<?php

use App\Enums\GradeLevel;

describe('GradeLevel Enum', function () {
    test('values method returns all grade level values', function () {
        $values = GradeLevel::values();

        expect($values)->toBe([
            'Kinder',
            'Grade 1',
            'Grade 2',
            'Grade 3',
            'Grade 4',
            'Grade 5',
            'Grade 6',
        ]);
    });

    test('label method returns the correct label for each grade', function () {
        expect(GradeLevel::KINDER->label())->toBe('Kinder');
        expect(GradeLevel::GRADE_1->label())->toBe('Grade 1');
        expect(GradeLevel::GRADE_2->label())->toBe('Grade 2');
        expect(GradeLevel::GRADE_3->label())->toBe('Grade 3');
        expect(GradeLevel::GRADE_4->label())->toBe('Grade 4');
        expect(GradeLevel::GRADE_5->label())->toBe('Grade 5');
        expect(GradeLevel::GRADE_6->label())->toBe('Grade 6');
    });

    test('order method returns correct order for each grade', function () {
        expect(GradeLevel::KINDER->order())->toBe(0);
        expect(GradeLevel::GRADE_1->order())->toBe(1);
        expect(GradeLevel::GRADE_2->order())->toBe(2);
        expect(GradeLevel::GRADE_3->order())->toBe(3);
        expect(GradeLevel::GRADE_4->order())->toBe(4);
        expect(GradeLevel::GRADE_5->order())->toBe(5);
        expect(GradeLevel::GRADE_6->order())->toBe(6);
    });

    test('nextGrade method returns the correct next grade', function () {
        expect(GradeLevel::KINDER->nextGrade())->toBe(GradeLevel::GRADE_1);
        expect(GradeLevel::GRADE_1->nextGrade())->toBe(GradeLevel::GRADE_2);
        expect(GradeLevel::GRADE_2->nextGrade())->toBe(GradeLevel::GRADE_3);
        expect(GradeLevel::GRADE_3->nextGrade())->toBe(GradeLevel::GRADE_4);
        expect(GradeLevel::GRADE_4->nextGrade())->toBe(GradeLevel::GRADE_5);
        expect(GradeLevel::GRADE_5->nextGrade())->toBe(GradeLevel::GRADE_6);
        expect(GradeLevel::GRADE_6->nextGrade())->toBeNull();
    });

    test('isHigherThan method correctly compares grade levels', function () {
        // Test Grade 6 is higher than all others
        expect(GradeLevel::GRADE_6->isHigherThan(GradeLevel::KINDER))->toBeTrue();
        expect(GradeLevel::GRADE_6->isHigherThan(GradeLevel::GRADE_1))->toBeTrue();
        expect(GradeLevel::GRADE_6->isHigherThan(GradeLevel::GRADE_2))->toBeTrue();
        expect(GradeLevel::GRADE_6->isHigherThan(GradeLevel::GRADE_3))->toBeTrue();
        expect(GradeLevel::GRADE_6->isHigherThan(GradeLevel::GRADE_4))->toBeTrue();
        expect(GradeLevel::GRADE_6->isHigherThan(GradeLevel::GRADE_5))->toBeTrue();
        expect(GradeLevel::GRADE_6->isHigherThan(GradeLevel::GRADE_6))->toBeFalse();

        // Test Grade 3 comparisons
        expect(GradeLevel::GRADE_3->isHigherThan(GradeLevel::KINDER))->toBeTrue();
        expect(GradeLevel::GRADE_3->isHigherThan(GradeLevel::GRADE_1))->toBeTrue();
        expect(GradeLevel::GRADE_3->isHigherThan(GradeLevel::GRADE_2))->toBeTrue();
        expect(GradeLevel::GRADE_3->isHigherThan(GradeLevel::GRADE_3))->toBeFalse();
        expect(GradeLevel::GRADE_3->isHigherThan(GradeLevel::GRADE_4))->toBeFalse();
        expect(GradeLevel::GRADE_3->isHigherThan(GradeLevel::GRADE_5))->toBeFalse();
        expect(GradeLevel::GRADE_3->isHigherThan(GradeLevel::GRADE_6))->toBeFalse();

        // Test Kinder is not higher than any
        expect(GradeLevel::KINDER->isHigherThan(GradeLevel::KINDER))->toBeFalse();
        expect(GradeLevel::KINDER->isHigherThan(GradeLevel::GRADE_1))->toBeFalse();
        expect(GradeLevel::KINDER->isHigherThan(GradeLevel::GRADE_6))->toBeFalse();
    });

    test('isLowerThan method correctly compares grade levels', function () {
        // Test Kinder is lower than all others
        expect(GradeLevel::KINDER->isLowerThan(GradeLevel::KINDER))->toBeFalse();
        expect(GradeLevel::KINDER->isLowerThan(GradeLevel::GRADE_1))->toBeTrue();
        expect(GradeLevel::KINDER->isLowerThan(GradeLevel::GRADE_2))->toBeTrue();
        expect(GradeLevel::KINDER->isLowerThan(GradeLevel::GRADE_3))->toBeTrue();
        expect(GradeLevel::KINDER->isLowerThan(GradeLevel::GRADE_4))->toBeTrue();
        expect(GradeLevel::KINDER->isLowerThan(GradeLevel::GRADE_5))->toBeTrue();
        expect(GradeLevel::KINDER->isLowerThan(GradeLevel::GRADE_6))->toBeTrue();

        // Test Grade 3 comparisons
        expect(GradeLevel::GRADE_3->isLowerThan(GradeLevel::KINDER))->toBeFalse();
        expect(GradeLevel::GRADE_3->isLowerThan(GradeLevel::GRADE_1))->toBeFalse();
        expect(GradeLevel::GRADE_3->isLowerThan(GradeLevel::GRADE_2))->toBeFalse();
        expect(GradeLevel::GRADE_3->isLowerThan(GradeLevel::GRADE_3))->toBeFalse();
        expect(GradeLevel::GRADE_3->isLowerThan(GradeLevel::GRADE_4))->toBeTrue();
        expect(GradeLevel::GRADE_3->isLowerThan(GradeLevel::GRADE_5))->toBeTrue();
        expect(GradeLevel::GRADE_3->isLowerThan(GradeLevel::GRADE_6))->toBeTrue();

        // Test Grade 6 is not lower than any
        expect(GradeLevel::GRADE_6->isLowerThan(GradeLevel::KINDER))->toBeFalse();
        expect(GradeLevel::GRADE_6->isLowerThan(GradeLevel::GRADE_1))->toBeFalse();
        expect(GradeLevel::GRADE_6->isLowerThan(GradeLevel::GRADE_6))->toBeFalse();
    });

    test('getAvailableGradesFor returns all grades for new student', function () {
        $grades = GradeLevel::getAvailableGradesFor(null);

        expect($grades)->toHaveCount(7);
        expect($grades)->toBe(GradeLevel::cases());
    });

    test('getAvailableGradesFor returns current and higher grades for existing student', function () {
        // Test from Kinder - should get all grades
        $grades = GradeLevel::getAvailableGradesFor(GradeLevel::KINDER);
        expect($grades)->toHaveCount(7);
        expect($grades)->toBe([
            GradeLevel::KINDER,
            GradeLevel::GRADE_1,
            GradeLevel::GRADE_2,
            GradeLevel::GRADE_3,
            GradeLevel::GRADE_4,
            GradeLevel::GRADE_5,
            GradeLevel::GRADE_6,
        ]);

        // Test from Grade 3 - should get Grade 3 and higher
        $grades = GradeLevel::getAvailableGradesFor(GradeLevel::GRADE_3);
        expect($grades)->toHaveCount(4);
        expect($grades)->toBe([
            GradeLevel::GRADE_3,
            GradeLevel::GRADE_4,
            GradeLevel::GRADE_5,
            GradeLevel::GRADE_6,
        ]);

        // Test from Grade 5 - should get Grade 5 and 6
        $grades = GradeLevel::getAvailableGradesFor(GradeLevel::GRADE_5);
        expect($grades)->toHaveCount(2);
        expect($grades)->toBe([
            GradeLevel::GRADE_5,
            GradeLevel::GRADE_6,
        ]);

        // Test from Grade 6 - should only get Grade 6
        $grades = GradeLevel::getAvailableGradesFor(GradeLevel::GRADE_6);
        expect($grades)->toHaveCount(1);
        expect($grades)->toBe([
            GradeLevel::GRADE_6,
        ]);
    });

    test('grade level can be created from string value', function () {
        $grade = GradeLevel::from('Kinder');
        expect($grade)->toBe(GradeLevel::KINDER);

        $grade = GradeLevel::from('Grade 1');
        expect($grade)->toBe(GradeLevel::GRADE_1);

        $grade = GradeLevel::from('Grade 6');
        expect($grade)->toBe(GradeLevel::GRADE_6);
    });

    test('tryFrom returns null for invalid values', function () {
        $grade = GradeLevel::tryFrom('Invalid Grade');
        expect($grade)->toBeNull();

        $grade = GradeLevel::tryFrom('Grade 7');
        expect($grade)->toBeNull();

        $grade = GradeLevel::tryFrom('');
        expect($grade)->toBeNull();
    });

    test('cases method returns all enum cases', function () {
        $cases = GradeLevel::cases();

        expect($cases)->toHaveCount(7);
        expect($cases[0])->toBe(GradeLevel::KINDER);
        expect($cases[1])->toBe(GradeLevel::GRADE_1);
        expect($cases[2])->toBe(GradeLevel::GRADE_2);
        expect($cases[3])->toBe(GradeLevel::GRADE_3);
        expect($cases[4])->toBe(GradeLevel::GRADE_4);
        expect($cases[5])->toBe(GradeLevel::GRADE_5);
        expect($cases[6])->toBe(GradeLevel::GRADE_6);
    });
});
