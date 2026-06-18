<?php

declare(strict_types=1);

namespace App\Services\Media\Contracts;

/**
 * Renders QR codes to PNG bytes. Abstracted so the engine can be stubbed
 * in tests without depending on the rendering library.
 */
interface QrGenerator
{
    /**
     * Render the given data to PNG image bytes.
     */
    public function png(string $data, int $size = 300): string;
}
