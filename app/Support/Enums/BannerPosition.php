<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Placement slot for a storefront banner.
 */
enum BannerPosition: string
{
    case Hero = 'hero';
    case Top = 'top';
    case Sidebar = 'sidebar';
    case Footer = 'footer';
    case Category = 'category';

    /**
     * Human-readable label for the position.
     */
    public function label(): string
    {
        return __('cms.banner_position.'.$this->value);
    }
}
