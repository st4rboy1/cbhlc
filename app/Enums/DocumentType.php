<?php

namespace App\Enums;

enum DocumentType: string
{
    case BIRTH_CERTIFICATE = 'birth_certificate';
    case REPORT_CARD = 'report_card';
    case FORM_138 = 'form_138';
    case GOOD_MORAL = 'good_moral';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::BIRTH_CERTIFICATE => 'Birth Certificate',
            self::REPORT_CARD => 'Report Card',
            self::FORM_138 => 'Form 138',
            self::GOOD_MORAL => 'Good Moral Certificate',
            self::OTHER => 'Other Document',
        };
    }
}
