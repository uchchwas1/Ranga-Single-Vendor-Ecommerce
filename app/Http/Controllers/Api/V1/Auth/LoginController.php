<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * Handles credential login.
 */
class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly AuthService $auth,
    ) {
    }

    /**
     * Attempt login; returns a token or a two-factor challenge.
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $result = $this->auth->attemptLogin(
            (string) $request->string('email'),
            (string) $request->string('password'),
            $request->ip(),
            $request->userAgent(),
        );

        if ($result->requiresTwoFactor()) {
            return new JsonResponse([
                'two_factor_required' => true,
                'challenge_token' => $result->challengeToken,
                'message' => __('auth.2fa.challenge_issued'),
            ]);
        }

        return new JsonResponse([
            'two_factor_required' => false,
            'token' => $result->token,
            'user' => new UserResource($result->user),
            'message' => __('auth.logged_in'),
        ]);
    }
}
