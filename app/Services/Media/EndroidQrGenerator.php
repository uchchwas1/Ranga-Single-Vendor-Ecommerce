<?php

declare(strict_types=1);

namespace App\Services\Media;

use App\Services\Media\Contracts\QrGenerator;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

/**
 * QR generator backed by endroid/qr-code.
 */
class EndroidQrGenerator implements QrGenerator
{
    /**
     * Render the given data to PNG image bytes.
     */
    public function png(string $data, int $size = 300): string
    {
        $result = new Builder(
            writer: new PngWriter(),
            data: $data,
            size: $size,
            margin: 10,
        );

        return $result->build()->getString();
    }
}
