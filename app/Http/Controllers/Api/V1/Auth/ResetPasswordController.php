<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * Handles token-based password resets.
 */
class ResetPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly AuthService $auth,
    ) {
    }

    /**
     * Reset the password using a broker token.
     */
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        /** @var array{email: string, password: string, password_confirmation: string, token: string} $credentials */
        $credentials = $request->validated();

        $this->auth->resetPassword($credentials);

        return new JsonResponse(['message' => __('passwords.reset')]);
    }
}
