<?php

use App\Enums\DocumentType;

test('document type enum has correct values', function () {
    expect(DocumentType::BIRTH_CERTIFICATE->value)->toBe('birth_certificate');
    expect(DocumentType::REPORT_CARD->value)->toBe('report_card');
    expect(DocumentType::FORM_138->value)->toBe('form_138');
    expect(DocumentType::GOOD_MORAL->value)->toBe('good_moral');
    expect(DocumentType::OTHER->value)->toBe('other');
});

test('document type enum labels are correct', function () {
    expect(DocumentType::BIRTH_CERTIFICATE->label())->toBe('Birth Certificate');
    expect(DocumentType::REPORT_CARD->label())->toBe('Report Card');
    expect(DocumentType::FORM_138->label())->toBe('Form 138');
    expect(DocumentType::GOOD_MORAL->label())->toBe('Good Moral Certificate');
    expect(DocumentType::OTHER->label())->toBe('Other Document');
});

test('document type enum can be created from string values', function () {
    expect(DocumentType::from('birth_certificate'))->toBe(DocumentType::BIRTH_CERTIFICATE);
    expect(DocumentType::from('report_card'))->toBe(DocumentType::REPORT_CARD);
    expect(DocumentType::from('form_138'))->toBe(DocumentType::FORM_138);
    expect(DocumentType::from('good_moral'))->toBe(DocumentType::GOOD_MORAL);
    expect(DocumentType::from('other'))->toBe(DocumentType::OTHER);
});

test('document type enum throws exception for invalid values', function () {
    DocumentType::from('invalid');
})->throws(ValueError::class);
