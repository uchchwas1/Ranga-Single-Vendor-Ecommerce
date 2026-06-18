<?php

declare(strict_types=1);

use App\Console\Commands\MarkAbandonedCarts;
use App\Console\Commands\RenewSubscriptions;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Flag abandoned carts and queue recovery emails every 15 minutes.
Schedule::command(MarkAbandonedCarts::class)->everyFifteenMinutes();

// Renew due subscriptions daily.
Schedule::command(RenewSubscriptions::class)->daily();
