<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A carrier shipping label generated for an order/shipment.
 *
 * @property string $id
 * @property string $order_id
 * @property string|null $shipment_id
 * @property string $label_url
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class ShippingLabel extends Model
{
    /** @use HasFactory<\Database\Factories\ShippingLabelFactory> */
    use HasFactory, HasUlids;

    /**
     * Only a created_at timestamp is stored.
     *
     * @var bool
     */
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'shipment_id',
        'label_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * The order the label belongs to.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The shipment the label is for, if any.
     *
     * @return BelongsTo<Shipment, $this>
     */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
