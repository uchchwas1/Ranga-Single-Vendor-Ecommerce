<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Setting;
use Illuminate\Support\Collection;

/**
 * Persistence boundary for platform settings.
 */
interface SettingRepositoryContract
{
    /**
     * All settings.
     *
     * @return Collection<int, Setting>
     */
    public function all(): Collection;

    /**
     * All settings within a group.
     *
     * @return Collection<int, Setting>
     */
    public function group(string $group): Collection;

    /**
     * All publicly exposable settings.
     *
     * @return Collection<int, Setting>
     */
    public function publicSettings(): Collection;

    /**
     * Find a single setting.
     */
    public function find(string $group, string $key): ?Setting;

    /**
     * Create or update a setting value.
     */
    public function put(string $group, string $key, mixed $value, ?bool $isPublic = null): Setting;
}
