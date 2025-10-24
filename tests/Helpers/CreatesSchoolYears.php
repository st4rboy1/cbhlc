<?php

namespace Tests\Helpers;

use App\Models\SchoolYear;

trait CreatesSchoolYears
{
    /**
     * Create or get a school year (safe for parallel tests)
     */
    protected function createSchoolYear(array $attributes): SchoolYear
    {
        return SchoolYear::firstOrCreate(
            ['name' => $attributes['name']],
            $attributes
        );
    }
}
