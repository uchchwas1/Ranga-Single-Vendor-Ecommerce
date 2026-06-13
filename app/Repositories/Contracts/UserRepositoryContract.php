<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

/**
 * Persistence boundary for User aggregates.
 */
interface UserRepositoryContract
{
    /**
     * Find a user by primary key.
     */
    public function find(string $id): ?User;

    /**
     * Find a user by email address.
     */
    public function findByEmail(string $email): ?User;

    /**
     * Find a user by phone number.
     */
    public function findByPhone(string $phone): ?User;

    /**
     * Find a user by referral code.
     */
    public function findByReferralCode(string $code): ?User;

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): User;

    /**
     * Update the given user.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $user, array $attributes): User;

    /**
     * Record metadata about a successful login.
     */
    public function recordLogin(User $user, ?string $ip): void;
}
