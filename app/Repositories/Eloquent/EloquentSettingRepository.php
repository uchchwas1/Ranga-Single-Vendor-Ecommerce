<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Setting;
use App\Repositories\Contracts\SettingRepositoryContract;
use Illuminate\Support\Collection;

/**
 * Eloquent implementation of the settings repository.
 */
class EloquentSettingRepository implements SettingRepositoryContract
{
    /**
     * All settings.
     *
     * @return Collection<int, Setting>
     */
    public function all(): Collection
    {
        /** @var Collection<int, Setting> */
        return Setting::query()->orderBy('group')->orderBy('key')->get();
    }

    /**
     * All settings within a group.
     *
     * @return Collection<int, Setting>
     */
    public function group(string $group): Collection
    {
        /** @var Collection<int, Setting> */
        return Setting::query()->where('group', $group)->orderBy('key')->get();
    }

    /**
     * All publicly exposable settings.
     *
     * @return Collection<int, Setting>
     */
    public function publicSettings(): Collection
    {
        /** @var Collection<int, Setting> */
        return Setting::query()->where('is_public', true)->orderBy('group')->orderBy('key')->get();
    }

    /**
     * Find a single setting.
     */
    public function find(string $group, string $key): ?Setting
    {
        return Setting::query()->where('group', $group)->where('key', $key)->first();
    }

    /**
     * Create or update a setting value.
     */
    public function put(string $group, string $key, mixed $value, ?bool $isPublic = null): Setting
    {
        $attributes = ['value' => $value];

        if ($isPublic !== null) {
            $attributes['is_public'] = $isPublic;
        }

        return Setting::query()->updateOrCreate(
            ['group' => $group, 'key' => $key],
            $attributes,
        );
    }
}
