<?php

namespace App\Casts;

use App\Services\CurrencyService;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class FormattedMoneyCast implements CastsAttributes
{
    /**
     * Cast the given value to a formatted currency string.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        $centsKey = $this->getCentsKey($key);
        $centsValue = $attributes[$centsKey] ?? null;

        // If no cents column exists, try to get the computed dollar value
        if ($centsValue === null) {
            $dollarKey = str_replace('formatted_', '', $key);
            $dollarValue = $model->getAttribute($dollarKey);

            if ($dollarValue !== null) {
                return CurrencyService::formatCents((int) ($dollarValue * 100));
            }

            $centsValue = 0;
        }

        if ($centsValue === null) {
            $centsValue = 0;
        }

        return CurrencyService::formatCents((int) $centsValue);
    }

    /**
     * Prepare the given value for storage.
     * This cast is read-only, so we don't modify storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        // This is a read-only cast for displaying formatted values
        // The actual storage is handled by the cents column
        return [];
    }

    /**
     * Get the corresponding cents column name.
     */
    private function getCentsKey(string $key): string
    {
        // Remove 'formatted_' prefix if present
        $baseKey = str_replace('formatted_', '', $key);

        return $baseKey.'_cents';
    }
}
