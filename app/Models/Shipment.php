<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A shipment dispatched for an order.
 *
 * @property string $id
 * @property string $order_id
 * @property string|null $tracking_number
 * @property string|null $carrier
 * @property string|null $carrier_url
 * @property \Illuminate\Support\Carbon|null $shipped_at
 * @property \Illuminate\Support\Carbon|null $estimated_delivery
 * @property \Illuminate\Support\Carbon|null $delivered_at
 * @property ShipmentStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Shipment extends Model
{
    /** @use HasFactory<\Database\Factories\ShipmentFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'tracking_number',
        'carrier',
        'carrier_url',
        'shipped_at',
        'estimated_delivery',
        'delivered_at',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'estimated_delivery' => 'date',
            'delivered_at' => 'datetime',
            'status' => ShipmentStatus::class,
        ];
    }

    /**
     * The order being shipped.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The tracking URL, derived from carrier_url + tracking_number when set.
     */
    public function trackingUrl(): ?string
    {
        if ($this->carrier_url === null || $this->tracking_number === null) {
            return $this->carrier_url;
        }

        return str_contains($this->carrier_url, '{tracking}')
            ? str_replace('{tracking}', $this->tracking_number, $this->carrier_url)
            : rtrim($this->carrier_url, '/').'/'.$this->tracking_number;
    }
}
