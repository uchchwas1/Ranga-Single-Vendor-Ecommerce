<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * Handles password reset link requests.
 */
class ForgotPasswordController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly AuthService $auth,
    ) {
    }

    /**
     * Queue a password reset link if the account exists.
     */
    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        $this->auth->sendPasswordResetLink((string) $request->string('email'));

        return new JsonResponse(['message' => __('passwords.sent_generic')]);
    }
}
