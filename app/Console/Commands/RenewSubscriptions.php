<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Services\Commerce\SubscriptionService;
use Illuminate\Console\Command;

/**
 * Renews subscriptions due for their next billing cycle.
 */
class RenewSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ranga:renew-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Advance and bill subscriptions due for renewal';

    /**
     * Execute the console command.
     */
    public function handle(SubscriptionService $subscriptions): int
    {
        $count = 0;

        Subscription::query()->dueForRenewal()->cursor()->each(function (Subscription $subscription) use ($subscriptions, &$count): void {
            $subscriptions->renew($subscription);
            $count++;
        });

        $this->info("Renewed {$count} subscription(s).");

        return self::SUCCESS;
    }
}
