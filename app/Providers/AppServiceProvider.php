<?php

declare(strict_types=1);

namespace App\Providers;

use App\Notifications\Senders\LogPushSender;
use App\Notifications\Senders\LogSmsSender;
use App\Notifications\Senders\LogWhatsAppSender;
use App\Notifications\Senders\PushSender;
use App\Notifications\Senders\SmsSender;
use App\Notifications\Senders\WhatsAppSender;
use App\Services\Invoice\Contracts\InvoiceRenderer;
use App\Services\Invoice\DompdfInvoiceRenderer;
use App\Services\Media\Contracts\QrGenerator;
use App\Services\Media\EndroidQrGenerator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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

        // Notification senders default to the log/database driver; bind a
        // real gateway implementation here per deployment.
        $this->app->bind(SmsSender::class, LogSmsSender::class);
        $this->app->bind(WhatsAppSender::class, LogWhatsAppSender::class);
        $this->app->bind(PushSender::class, LogPushSender::class);
        $this->app->bind(QrGenerator::class, EndroidQrGenerator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePasswordRules();
        $this->configureRateLimiting();
        $this->configureHorizonGate();
    }

    /**
     * Restrict the Horizon dashboard to admin roles.
     */
    private function configureHorizonGate(): void
    {
        Gate::define('viewHorizon', static fn ($user): bool => method_exists($user, 'hasRole')
            && $user->hasRole(['admin', 'super-admin']));
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
