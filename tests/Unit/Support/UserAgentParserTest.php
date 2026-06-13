<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\UserAgentParser;
use PHPUnit\Framework\TestCase;

class UserAgentParserTest extends TestCase
{
    public function test_parses_a_desktop_chrome_user_agent(): void
    {
        $parsed = UserAgentParser::parse(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36'
        );

        $this->assertSame('desktop', $parsed['device']);
        $this->assertSame('Chrome', $parsed['browser']);
        $this->assertSame('Windows', $parsed['os']);
    }

    public function test_parses_an_android_mobile_user_agent(): void
    {
        $parsed = UserAgentParser::parse(
            'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Mobile Safari/537.36'
        );

        $this->assertSame('mobile', $parsed['device']);
        $this->assertSame('Android', $parsed['os']);
    }

    public function test_handles_null_user_agent(): void
    {
        $parsed = UserAgentParser::parse(null);

        $this->assertSame('unknown', $parsed['device']);
        $this->assertSame('Unknown', $parsed['browser']);
        $this->assertSame('Unknown', $parsed['os']);
    }
}
