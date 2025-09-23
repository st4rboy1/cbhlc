<?php

namespace App\Services;

use App\Contracts\Services\ServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

abstract class BaseService implements ServiceInterface
{
    /**
     * The model instance
     */
    protected Model $model;

    /**
     * BaseService constructor.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get paginated results
     *
     * @return mixed
     */
    public function paginate(array $filters = [], int $perPage = 15)
    {
        $query = $this->model->newQuery();

        $this->applyFilters($query, $filters);

        return $query->paginate($perPage);
    }

    /**
     * Find a single record by ID
     *
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new record
     *
     * @return mixed
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->model->create($data);
        });
    }

    /**
     * Update an existing record
     *
     * @return mixed
     */
    public function update(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $record = $this->find($id);
            $record->update($data);

            return $record->fresh();
        });
    }

    /**
     * Delete a record
     */
    public function delete(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $record = $this->find($id);

            return $record->delete();
        });
    }

    /**
     * Apply filters to the query
     *
     * @param  mixed  $query
     */
    protected function applyFilters($query, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($field, $value);
                } else {
                    $query->where($field, $value);
                }
            }
        }
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, array $data = []): void
    {
        Log::info("Service action: {$action}", [
            'service' => static::class,
            'user_id' => auth()->id(),
            'data' => $data,
        ]);
    }
}
