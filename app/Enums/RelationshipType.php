<?php

namespace App\Enums;

enum RelationshipType: string
{
    case FATHER = 'father';
    case MOTHER = 'mother';
    case GUARDIAN = 'guardian';
    case GRANDPARENT = 'grandparent';
    case OTHER = 'other';

    /**
     * Get the display label for the relationship type
     */
    public function label(): string
    {
        return match ($this) {
            self::FATHER => 'Father',
            self::MOTHER => 'Mother',
            self::GUARDIAN => 'Guardian',
            self::GRANDPARENT => 'Grandparent',
            self::OTHER => 'Other',
        };
    }

    /**
     * Get all values as array for validation
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all relationship types as array with value and label
     */
    public static function options(): array
    {
        return collect(self::cases())->map(fn ($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ])->toArray();
    }
}
