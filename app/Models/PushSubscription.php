<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A web-push subscription endpoint registered by a user's device.
 *
 * @property string $id
 * @property string|null $user_id
 * @property string $endpoint
 * @property string|null $p256dh
 * @property string|null $auth
 * @property string|null $device_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PushSubscription extends Model
{
    /** @use HasFactory<\Database\Factories\PushSubscriptionFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh',
        'auth',
        'device_type',
    ];

    /**
     * The owning user.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
