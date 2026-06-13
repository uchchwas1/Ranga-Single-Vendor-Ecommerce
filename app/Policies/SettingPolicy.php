<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Authorization rules for white-label platform settings.
 */
class SettingPolicy
{
    /**
     * Whether the actor may view all settings (including private).
     */
    public function viewAny(User $actor): bool
    {
        return $actor->hasRole(['admin', 'super-admin']);
    }

    /**
     * Whether the actor may create or update settings.
     */
    public function manage(User $actor): bool
    {
        return $actor->hasRole(['admin', 'super-admin']);
    }
}
