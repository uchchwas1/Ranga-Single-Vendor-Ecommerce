<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Marketing\SendCartRecoveryEmail;
use App\Models\CartAbandonment;
use App\Services\Marketing\CartAbandonmentService;
use Illuminate\Console\Command;

/**
 * Flags idle carts as abandoned and queues recovery emails.
 *
 * Scheduled to run periodically (see routes/console.php).
 */
class MarkAbandonedCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ranga:mark-abandoned-carts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flag idle carts as abandoned and queue recovery emails';

    /**
     * Execute the console command.
     */
    public function handle(CartAbandonmentService $service): int
    {
        $flagged = $service->flagAbandoned();

        $flagged->each(static function (CartAbandonment $abandonment): void {
            if ($abandonment->email !== null) {
                SendCartRecoveryEmail::dispatch($abandonment->id);
            }
        });

        $this->info("Flagged {$flagged->count()} abandoned cart(s).");

        return self::SUCCESS;
    }
}
