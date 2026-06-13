<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * Handles account registration.
 */
class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly AuthService $auth,
    ) {
    }

    /**
     * Register a new account and send the verification email.
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        /** @var array{name: string, email: string, phone?: string|null, password: string, locale?: string|null, referral_code?: string|null} $data */
        $data = $request->validated();

        $user = $this->auth->register($data);

        return (new UserResource($user))
            ->additional(['message' => __('auth.registered')])
            ->response()
            ->setStatusCode(201);
    }
}
