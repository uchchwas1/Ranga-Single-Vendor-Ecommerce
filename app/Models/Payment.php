<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\PaymentGateway;
use App\Support\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A payment record against an order.
 *
 * @property string $id
 * @property string $order_id
 * @property string|null $user_id
 * @property PaymentGateway $gateway
 * @property string|null $gateway_transaction_id
 * @property string $amount
 * @property string $currency
 * @property PaymentStatus $status
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property array<string, mixed>|null $payload
 * @property array<string, mixed>|null $gateway_response
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Payment extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_id',
        'user_id',
        'gateway',
        'gateway_transaction_id',
        'amount',
        'currency',
        'status',
        'paid_at',
        'payload',
        'gateway_response',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gateway' => PaymentGateway::class,
            'status' => PaymentStatus::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'payload' => 'array',
            'gateway_response' => 'array',
        ];
    }

    /**
     * The order being paid.
     *
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * The paying user, if not a guest.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Gateway interaction logs.
     *
     * @return HasMany<PaymentGatewayLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(PaymentGatewayLog::class);
    }
}
