<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\SocialProvider;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A linked OAuth provider account.
 *
 * @property string $id
 * @property string $user_id
 * @property SocialProvider $provider
 * @property string $provider_id
 * @property string|null $token
 * @property string|null $refresh_token
 * @property string|null $avatar
 */
class SocialAccount extends Model
{
    /** @use HasFactory<\Database\Factories\SocialAccountFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'token',
        'refresh_token',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'token',
        'refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provider' => SocialProvider::class,
            'token' => 'encrypted',
            'refresh_token' => 'encrypted',
        ];
    }

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
