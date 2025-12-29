<?php

namespace App\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    /**
     * @var Model
     */
    protected Model $model;

    /**
     * Get all records
     *
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    /**
     * Find a record by ID
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Model|null
     */
    public function find(int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id, $columns);
    }

    /**
     * Find a record by ID or fail
     *
     * @param int $id
     * @param array $columns
     * @param array $relations
     * @return Model
     */
    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): Model
    {
        return $this->model->with($relations)->findOrFail($id, $columns);
    }

    /**
     * Find records by field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function findBy(string $field, mixed $value, array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->where($field, $value)->get($columns);
    }

    /**
     * Find first record by field
     *
     * @param string $field
     * @param mixed $value
     * @param array $columns
     * @param array $relations
     * @return Model|null
     */
    public function findOneBy(string $field, mixed $value, array $columns = ['*'], array $relations = []): ?Model
    {
        return $this->model->with($relations)->where($field, $value)->first($columns);
    }

    /**
     * Create a new record
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update a record
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $record = $this->findOrFail($id);
        return $record->update($data);
    }

    /**
     * Delete a record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }

    /**
     * Get paginated records
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    /**
     * Count records
     *
     * @return int
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Check if record exists
     *
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    /**
     * Get the model instance
     *
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * Create a new query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        return $this->model->newQuery();
    }

    /**
     * Alias for query() method
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQuery()
    {
        return $this->query();
    }

    /**
     * Get all records paginated with optional relations and search
     *
     * @param int $perPage
     * @param array $relations
     * @param string|null $search
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllPaginated(int $perPage = 15, array $relations = [], ?string $search = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->model->query();

        if (!empty($relations)) {
            $query->with($relations);
        }

        if ($search) {
            // Basic search on 'name' or 'title' column if exists
            $query->where(function ($q) use ($search) {
                $fillable = $this->model->getFillable();
                if (in_array('name', $fillable)) {
                    $q->orWhere('name', 'like', "%{$search}%");
                }
                if (in_array('title', $fillable)) {
                    $q->orWhere('title', 'like', "%{$search}%");
                }
            });
        }

        return $query->latest()->paginate($perPage);
    }
}

