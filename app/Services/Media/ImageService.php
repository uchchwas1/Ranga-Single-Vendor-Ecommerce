<?php

declare(strict_types=1);

namespace App\Services\Media;

/**
 * Builds on-the-fly optimised image URLs (imgproxy), serving WebP with
 * resize parameters. When an imgproxy key/salt is configured the URL is
 * signed; otherwise the source/CDN URL is returned with a query hint.
 */
class ImageService
{
    /**
     * Build an optimised URL for a stored image path.
     */
    public function url(?string $path, int $width = 0, int $height = 0, ?string $format = null): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $format ??= (string) config('ranga.images.format', 'webp');
        $source = $this->sourceUrl($path);
        $key = (string) config('ranga.images.imgproxy_key', '');
        $salt = (string) config('ranga.images.imgproxy_salt', '');
        $base = rtrim((string) config('ranga.images.imgproxy_url', ''), '/');

        $processing = "/rs:fit:{$width}:{$height}/f:{$format}";
        $encodedSource = rtrim(strtr(base64_encode($source), '+/', '-_'), '=');
        $pathPart = $processing.'/'.$encodedSource;

        if ($key === '' || $salt === '') {
            // Unsigned: rely on imgproxy's insecure mode or a CDN fallback.
            return $base !== '' ? $base.'/insecure'.$pathPart : $source;
        }

        $signature = $this->sign($pathPart, $key, $salt);

        return $base.'/'.$signature.$pathPart;
    }

    /**
     * Resolve a stored path to an absolute source URL.
     */
    private function sourceUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return rtrim((string) config('ranga.images.source_base', ''), '/').'/'.ltrim($path, '/');
    }

    /**
     * Compute the imgproxy URL signature.
     */
    private function sign(string $pathPart, string $key, string $salt): string
    {
        $binKey = (string) hex2bin($key);
        $binSalt = (string) hex2bin($salt);

        $digest = hash_hmac('sha256', $binSalt.$pathPart, $binKey, true);

        return rtrim(strtr(base64_encode($digest), '+/', '-_'), '=');
    }
}
