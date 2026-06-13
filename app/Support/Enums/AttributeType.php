<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * The kind of value an attribute holds, driving how it renders in the UI.
 */
enum AttributeType: string
{
    case Color = 'color';
    case Size = 'size';
    case Material = 'material';
    case Weight = 'weight';
    case Text = 'text';

    /**
     * Human-readable label for the attribute type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Color => __('catalogue.attribute_type.color'),
            self::Size => __('catalogue.attribute_type.size'),
            self::Material => __('catalogue.attribute_type.material'),
            self::Weight => __('catalogue.attribute_type.weight'),
            self::Text => __('catalogue.attribute_type.text'),
        };
    }

    /**
     * Whether values of this type carry a hex colour code in their meta.
     */
    public function hasSwatch(): bool
    {
        return $this === self::Color;
    }
}
