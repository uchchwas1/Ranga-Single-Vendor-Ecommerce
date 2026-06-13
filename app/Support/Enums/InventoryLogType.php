<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * The reason an inventory quantity changed.
 */
enum InventoryLogType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Adjustment = 'adjustment';
    case Return = 'return';

    /**
     * Human-readable label for the log type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Purchase => __('catalogue.inventory_log_type.purchase'),
            self::Sale => __('catalogue.inventory_log_type.sale'),
            self::Adjustment => __('catalogue.inventory_log_type.adjustment'),
            self::Return => __('catalogue.inventory_log_type.return'),
        };
    }

    /**
     * Whether this movement increases the on-hand quantity.
     */
    public function isInbound(): bool
    {
        return match ($this) {
            self::Purchase, self::Return => true,
            self::Sale, self::Adjustment => false,
        };
    }
}
