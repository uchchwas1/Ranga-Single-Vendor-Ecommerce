<?php

declare(strict_types=1);

namespace App\Services\Support;

use Closure;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;

/**
 * Thin tagged-cache helper. Falls back gracefully to untagged caching
 * when the active store does not support tags (e.g. the file driver).
 */
class CacheService
{
    /**
     * Remember a value under a key, tagged for bulk invalidation.
     *
     * @template TValue
     *
     * @param  list<string>  $tags
     * @param  Closure(): TValue  $callback
     * @return TValue
     */
    public function remember(array $tags, string $key, int $ttl, Closure $callback): mixed
    {
        if ($this->taggable()) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($this->flatKey($tags, $key), $ttl, $callback);
    }

    /**
     * Flush all cache entries associated with the given tag(s).
     *
     * @param  list<string>  $tags
     */
    public function flush(array $tags): void
    {
        if ($this->taggable()) {
            Cache::tags($tags)->flush();
        }
        // Untagged stores rely on TTL expiry; nothing to flush selectively.
    }

    /**
     * Whether the active cache store supports tagging.
     */
    private function taggable(): bool
    {
        return Cache::getStore() instanceof TaggableStore;
    }

    /**
     * Build a namespaced key for non-taggable stores.
     *
     * @param  list<string>  $tags
     */
    private function flatKey(array $tags, string $key): string
    {
        return implode(':', $tags).':'.$key;
    }
}
