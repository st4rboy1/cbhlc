<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class FullNameCast implements CastsAttributes
{
    /**
     * Cast the given value to a formatted full name.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        $firstName = trim($attributes['first_name'] ?? '');
        $middleName = trim($attributes['middle_name'] ?? '');
        $lastName = trim($attributes['last_name'] ?? '');

        $nameParts = array_filter([$firstName, $middleName, $lastName]);

        return implode(' ', $nameParts);
    }

    /**
     * Prepare the given value for storage.
     * This cast is read-only, so we don't modify storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        // This is a read-only cast for displaying full names
        // The actual storage is handled by individual name columns
        return [];
    }
}
