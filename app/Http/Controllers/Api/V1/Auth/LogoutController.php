<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles token revocation.
 */
class LogoutController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly AuthService $auth,
    ) {
    }

    /**
     * Revoke the current access token.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $this->auth->logout($user);

        return new JsonResponse(['message' => __('auth.logged_out')]);
    }
}
