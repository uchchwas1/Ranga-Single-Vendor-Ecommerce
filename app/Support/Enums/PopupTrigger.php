<?php

declare(strict_types=1);

namespace App\Support\Enums;

/**
 * What causes a marketing popup to display.
 */
enum PopupTrigger: string
{
    case Delay = 'delay';
    case ExitIntent = 'exit_intent';
    case Scroll = 'scroll';

    /**
     * Human-readable label for the trigger.
     */
    public function label(): string
    {
        return __('cms.popup_trigger.'.$this->value);
    }
}
