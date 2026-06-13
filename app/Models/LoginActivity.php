<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\LoginStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An audit record for a login attempt.
 *
 * @property string $id
 * @property string|null $user_id
 * @property string|null $ip
 * @property string|null $device
 * @property string|null $browser
 * @property string|null $os
 * @property string|null $city
 * @property string|null $country
 * @property LoginStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class LoginActivity extends Model
{
    /** @use HasFactory<\Database\Factories\LoginActivityFactory> */
    use HasFactory, HasUlids;

    public const null UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'ip',
        'device',
        'browser',
        'os',
        'city',
        'country',
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
            'status' => LoginStatus::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * The user this activity belongs to.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
