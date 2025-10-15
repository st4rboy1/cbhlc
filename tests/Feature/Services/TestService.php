<?php

namespace Tests\Feature\Services;

use App\Models\Student;
use App\Services\BaseService;

class TestService extends BaseService
{
    public function __construct()
    {
        parent::__construct(new Student);
    }

    // Expose protected method for testing
    public function testApplyFilters($query, array $filters): void
    {
        $this->applyFilters($query, $filters);
    }
}
