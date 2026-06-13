<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Minimal user-agent string parser for login activity auditing.
 */
final class UserAgentParser
{
    /**
     * Parse a user agent string into device, browser and OS parts.
     *
     * @return array{device: string, browser: string, os: string}
     */
    public static function parse(?string $userAgent): array
    {
        $ua = $userAgent ?? '';

        $os = match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Mac OS') || str_contains($ua, 'Macintosh') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Unknown',
        };

        $browser = match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Chrome/') => 'Chrome',
            str_contains($ua, 'Safari/') && str_contains($ua, 'Version/') => 'Safari',
            str_contains($ua, 'Firefox/') => 'Firefox',
            default => 'Unknown',
        };

        $device = match (true) {
            str_contains($ua, 'Mobile') || str_contains($ua, 'Android') || str_contains($ua, 'iPhone') => 'mobile',
            str_contains($ua, 'Tablet') || str_contains($ua, 'iPad') => 'tablet',
            $ua === '' => 'unknown',
            default => 'desktop',
        };

        return ['device' => $device, 'browser' => $browser, 'os' => $os];
    }
}
