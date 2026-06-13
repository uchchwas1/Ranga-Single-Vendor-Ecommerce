<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Authorization rules for user profiles.
 */
class UserPolicy
{
    /**
     * Whether the actor may view the given profile.
     */
    public function view(User $actor, User $target): bool
    {
        return $actor->id === $target->id || $actor->hasRole(['admin', 'super-admin']);
    }

    /**
     * Whether the actor may update the given profile.
     */
    public function update(User $actor, User $target): bool
    {
        return $actor->id === $target->id || $actor->hasRole(['admin', 'super-admin']);
    }
}
