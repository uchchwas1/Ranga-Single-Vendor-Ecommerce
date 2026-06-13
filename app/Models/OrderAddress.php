<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\AddressType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A shipping or billing address captured for an order.
 *
 * @property string $id
 * @property string $order_id
 * @property AddressType $type
 * @property string $name
 * @property string $phone
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property string $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string $country_code
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class OrderAddress extends Model
{
    /** @use HasFactory<\Database\Factories\OrderAddressFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'type',
        'name',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country_code',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => AddressType::class,
        ];
    }

    /**
     * The owning order.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
