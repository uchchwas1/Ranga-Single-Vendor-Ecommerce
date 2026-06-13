<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Repositories\Contracts\SettingRepositoryContract;
use Illuminate\Support\Facades\Cache;

/**
 * Application service for reading and writing white-label settings,
 * with a cache in front of the repository.
 */
class SettingsService
{
    private const int CACHE_TTL_SECONDS = 3600;

    private const string CACHE_PREFIX = 'settings';

    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly SettingRepositoryContract $settings,
    ) {
    }

    /**
     * Read a single setting value.
     */
    public function get(string $group, string $key, mixed $default = null): mixed
    {
        $value = Cache::remember(
            sprintf('%s.%s.%s', self::CACHE_PREFIX, $group, $key),
            self::CACHE_TTL_SECONDS,
            fn (): mixed => $this->settings->find($group, $key)?->value,
        );

        return $value ?? $default;
    }

    /**
     * Write a setting value and bust its cache entry.
     */
    public function set(string $group, string $key, mixed $value, ?bool $isPublic = null): void
    {
        $this->settings->put($group, $key, $value, $isPublic);

        Cache::forget(sprintf('%s.%s.%s', self::CACHE_PREFIX, $group, $key));
        Cache::forget(sprintf('%s.public', self::CACHE_PREFIX));
    }

    /**
     * All settings grouped for the admin UI.
     *
     * @return array<string, array<string, mixed>>
     */
    public function all(): array
    {
        $grouped = [];

        foreach ($this->settings->all() as $setting) {
            $grouped[$setting->group][$setting->key] = $setting->value;
        }

        return $grouped;
    }

    /**
     * Publicly exposable settings (brand, currency, locale, ...).
     *
     * @return array<string, array<string, mixed>>
     */
    public function publicSettings(): array
    {
        /** @var array<string, array<string, mixed>> */
        return Cache::remember(
            sprintf('%s.public', self::CACHE_PREFIX),
            self::CACHE_TTL_SECONDS,
            function (): array {
                $grouped = [];

                foreach ($this->settings->publicSettings() as $setting) {
                    $grouped[$setting->group][$setting->key] = $setting->value;
                }

                return $grouped;
            },
        );
    }
}
