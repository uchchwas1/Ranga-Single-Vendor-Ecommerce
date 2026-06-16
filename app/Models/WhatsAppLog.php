<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A record of an outbound WhatsApp message.
 *
 * @property string $id
 * @property string $to
 * @property string $template
 * @property array<string, mixed>|null $variables
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class WhatsAppLog extends Model
{
    /** @use HasFactory<\Database\Factories\WhatsAppLogFactory> */
    use HasFactory, HasUlids;

    /**
     * The database table.
     *
     * @var string
     */
    protected $table = 'whatsapp_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'to',
        'template',
        'variables',
        'status',
        'sent_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'sent_at' => 'datetime',
        ];
    }
}
