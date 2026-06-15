<?php

declare(strict_types=1);

namespace App\Http\Resources\Cms;

use App\Models\Popup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API representation of a marketing popup.
 *
 * @mixin Popup
 */
class PopupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'trigger_type' => $this->trigger_type->value,
            'trigger_delay' => $this->trigger_delay,
            'show_once' => $this->show_once,
        ];
    }
}
