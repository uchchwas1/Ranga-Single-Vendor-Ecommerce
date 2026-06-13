<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorConfirmRequest;
use App\Http\Requests\Auth\TwoFactorVerifyRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Auth\AuthService;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles TOTP two-factor enrolment and login verification.
 */
class TwoFactorController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly AuthService $auth,
        private readonly TwoFactorService $twoFactor,
    ) {
    }

    /**
     * Begin two-factor enrolment for the authenticated user.
     */
    public function enable(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $payload = $this->twoFactor->enable($user);

        return new JsonResponse([
            'secret' => $payload['secret'],
            'otpauth_url' => $payload['otpauth_url'],
            'recovery_codes' => $payload['recovery_codes'],
            'message' => __('auth.2fa.enabled_pending_confirmation'),
        ]);
    }

    /**
     * Confirm enrolment with a TOTP code.
     */
    public function confirm(TwoFactorConfirmRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $this->twoFactor->confirm($user, (string) $request->string('code'))) {
            return new JsonResponse([
                'message' => __('auth.2fa.code_invalid'),
            ], 422);
        }

        return new JsonResponse(['message' => __('auth.2fa.confirmed')]);
    }

    /**
     * Disable two-factor authentication.
     */
    public function disable(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->twoFactor->disable($user);

        return new JsonResponse(['message' => __('auth.2fa.disabled')]);
    }

    /**
     * Complete a pending two-factor login challenge.
     */
    public function verify(TwoFactorVerifyRequest $request): JsonResponse
    {
        $result = $this->auth->verifyTwoFactorChallenge(
            (string) $request->string('challenge_token'),
            (string) $request->string('code'),
            $request->ip(),
            $request->userAgent(),
        );

        return new JsonResponse([
            'token' => $result->token,
            'user' => new UserResource($result->user),
            'message' => __('auth.logged_in'),
        ]);
    }
}
