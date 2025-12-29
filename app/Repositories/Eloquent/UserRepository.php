<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Find user by phone
     */
    public function findByPhone(string $phone): ?User
    {
        return $this->model->where('phone', $phone)->first();
    }

    /**
     * Find user by Instagram ID
     */
    public function findByInstagramId(string $instagramId): ?User
    {
        return $this->model->where('instagram_id', $instagramId)->first();
    }

    /**
     * Get admin users
     */
    public function getAdmins(): Collection
    {
        return $this->model->where('is_admin', true)->get();
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(int $userId): bool
    {
        return $this->model->where('id', $userId)
            ->where('is_admin', true)
            ->exists();
    }
}

