<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SocialLoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\SocialAuthService;
use App\Support\Enums\SocialProvider;
use Illuminate\Http\JsonResponse;

/**
 * Handles OAuth token exchange for Google / Facebook.
 */
class SocialAuthController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SocialAuthService $socialAuth,
    ) {
    }

    /**
     * Exchange a provider access token for a Sanctum token.
     */
    public function __invoke(SocialLoginRequest $request, SocialProvider $provider): JsonResponse
    {
        $result = $this->socialAuth->loginWithToken(
            $provider,
            (string) $request->string('access_token'),
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
