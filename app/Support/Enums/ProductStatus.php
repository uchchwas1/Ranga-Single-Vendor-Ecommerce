<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * Lifecycle status of a catalogue product.
 */
enum ProductStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Scheduled = 'scheduled';
    case Archived = 'archived';

    /**
     * Human-readable label for the status.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => __('catalogue.product_status.draft'),
            self::Active => __('catalogue.product_status.active'),
            self::Scheduled => __('catalogue.product_status.scheduled'),
            self::Archived => __('catalogue.product_status.archived'),
        };
    }

    /**
     * Statuses that may be shown to customers in the storefront.
     *
     * @return list<self>
     */
    public static function publiclyVisible(): array
    {
        return [self::Active];
    }
}
