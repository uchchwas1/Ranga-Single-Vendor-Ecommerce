<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Enums\InventoryLogType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An audit record of a single inventory movement.
 *
 * @property string $id
 * @property string $inventory_id
 * @property InventoryLogType $type
 * @property int $quantity_before
 * @property int $quantity_after
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property string|null $note
 * @property string|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class InventoryLog extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryLogFactory> */
    use HasFactory, HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'inventory_id',
        'type',
        'quantity_before',
        'quantity_after',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => InventoryLogType::class,
            'quantity_before' => 'integer',
            'quantity_after' => 'integer',
        ];
    }

    /**
     * The inventory row this log belongs to.
     *
     * @return BelongsTo<Inventory, $this>
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class);
    }

    /**
     * The staff user who made the change, if any.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
