<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Find user by email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find user by phone
     *
     * @param string $phone
     * @return User|null
     */
    public function findByPhone(string $phone): ?User;

    /**
     * Find user by Instagram ID
     *
     * @param string $instagramId
     * @return User|null
     */
    public function findByInstagramId(string $instagramId): ?User;

    /**
     * Get admin users
     *
     * @return Collection
     */
    public function getAdmins(): Collection;

    /**
     * Check if user is admin
     *
     * @param int $userId
     * @return bool
     */
    public function isAdmin(int $userId): bool;
}

