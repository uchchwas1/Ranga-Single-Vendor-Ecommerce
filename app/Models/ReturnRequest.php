<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\RefundMethod;
use App\Support\Enums\ReturnStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A customer-initiated return request.
 *
 * @property string $id
 * @property string $order_id
 * @property string|null $order_item_id
 * @property string|null $user_id
 * @property string $reason
 * @property string|null $description
 * @property list<string>|null $images
 * @property ReturnStatus $status
 * @property string|null $admin_note
 * @property RefundMethod|null $refund_method
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ReturnRequest extends Model
{
    /** @use HasFactory<\Database\Factories\ReturnRequestFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'order_item_id',
        'user_id',
        'reason',
        'description',
        'images',
        'status',
        'admin_note',
        'refund_method',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'images' => 'array',
            'status' => ReturnStatus::class,
            'refund_method' => RefundMethod::class,
        ];
    }

    /**
     * The order being returned against.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The specific order item, if a partial return.
     *
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * The requesting user, if not a guest.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Refunds issued for this return.
     *
     * @return HasMany<Refund, $this>
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }
}
