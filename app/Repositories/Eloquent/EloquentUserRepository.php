<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Support\Facades\Date;

/**
 * Eloquent implementation of the user repository.
 */
class EloquentUserRepository implements UserRepositoryContract
{
    /**
     * Find a user by primary key.
     */
    public function find(string $id): ?User
    {
        return User::query()->find($id);
    }

    /**
     * Find a user by email address.
     */
    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    /**
     * Find a user by phone number.
     */
    public function findByPhone(string $phone): ?User
    {
        return User::query()->where('phone', $phone)->first();
    }

    /**
     * Find a user by referral code.
     */
    public function findByReferralCode(string $code): ?User
    {
        return User::query()->where('referral_code', $code)->first();
    }

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes): User
    {
        return User::query()->create($attributes);
    }

    /**
     * Update the given user.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function update(User $user, array $attributes): User
    {
        $user->fill($attributes);
        $user->save();

        return $user->refresh();
    }

    /**
     * Record metadata about a successful login.
     */
    public function recordLogin(User $user, ?string $ip): void
    {
        $user->forceFill([
            'last_login_at' => Date::now(),
            'last_login_ip' => $ip,
        ])->save();
    }
}
