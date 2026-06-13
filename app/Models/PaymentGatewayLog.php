<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An audit record of a single gateway request/response cycle.
 *
 * @property string $id
 * @property string $payment_id
 * @property string $event
 * @property array<string, mixed>|null $request
 * @property array<string, mixed>|null $response
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class PaymentGatewayLog extends Model
{
    /** @use HasFactory<\Database\Factories\PaymentGatewayLogFactory> */
    use HasFactory, HasUlids;

    /**
     * The database table.
     *
     * @var string
     */
    protected $table = 'payment_gateways_log';

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
        'payment_id',
        'event',
        'request',
        'response',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'request' => 'array',
            'response' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * The payment this log belongs to.
     *
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
