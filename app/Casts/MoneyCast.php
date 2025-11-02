<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MoneyCast implements CastsAttributes
{
    /**
     * Cast the given value to the application's configured currency unit.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): float
    {
        $centsKey = $this->getCentsKey($key);
        $centsValue = $attributes[$centsKey] ?? 0;

        if ($centsValue === 0) {
            return 0.0;
        }

        $divisor = 10 ** config('currency.default.decimal_places', 2);

        return (float) ($centsValue / $divisor);
    }

    /**
     * Prepare the given value for storage in cents.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $centsKey = $this->getCentsKey($key);

        if ($value === null) {
            return [$centsKey => null];
        }

        $divisor = 10 ** config('currency.default.decimal_places', 2);

        return [$centsKey => (int) ((float) $value * $divisor)];
    }

    /**
     * Get the corresponding cents column name.
     */
    private function getCentsKey(string $key): string
    {
        return $key.'_cents';
    }
}
