<?php

namespace App\Contracts\Services;

interface ServiceInterface
{
    /**
     * Get paginated results
     *
     * @return mixed
     */
    public function paginate(array $filters = [], int $perPage = 15);

    /**
     * Find a single record by ID
     *
     * @return mixed
     */
    public function find(int $id);

    /**
     * Create a new record
     *
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update an existing record
     *
     * @return mixed
     */
    public function update(int $id, array $data);

    /**
     * Delete a record
     */
    public function delete(int $id): bool;
}
