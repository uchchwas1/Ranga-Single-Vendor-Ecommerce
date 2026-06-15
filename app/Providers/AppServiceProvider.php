<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Invoice\Contracts\InvoiceRenderer;
use App\Services\Invoice\DompdfInvoiceRenderer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

/**
 * Core application bootstrapping.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(InvoiceRenderer::class, DompdfInvoiceRenderer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePasswordRules();
        $this->configureRateLimiting();
    }

    /**
     * Default password strength rules.
     */
    private function configurePasswordRules(): void
    {
        Password::defaults(static function (): Password {
            $rule = Password::min(8);

            return app()->isProduction()
                ? $rule->mixedCase()->numbers()->uncompromised()
                : $rule;
        });
    }

    /**
     * Named rate limiters used across the API (security checklist).
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('api', static function (Request $request): Limit {
            $user = $request->user();

            return Limit::perMinute(60)->by(
                $user instanceof Authenticatable
                    ? (string) $user->getAuthIdentifier()
                    : (string) $request->ip(),
            );
        });

        RateLimiter::for('auth', static function (Request $request): Limit {
            return Limit::perMinute(5)->by((string) $request->ip());
        });

        RateLimiter::for('password-reset', static function (Request $request): Limit {
            return Limit::perMinutes(15, 3)->by((string) $request->ip());
        });

        RateLimiter::for('search', static function (Request $request): Limit {
            return Limit::perMinute(30)->by((string) $request->ip());
        });
    }
}
