<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles signed email verification links and re-sends.
 */
class EmailVerificationController extends Controller
{
    /**
     * Verify an email address from a signed URL.
     */
    public function verify(Request $request, string $id, string $hash): JsonResponse
    {
        /** @var User $user */
        $user = User::query()->findOrFail($id);

        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            return new JsonResponse(['message' => __('auth.verification.invalid')], 403);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();

            event(new Verified($user));
        }

        return new JsonResponse(['message' => __('auth.verification.verified')]);
    }

    /**
     * Re-send the verification email to the authenticated user.
     */
    public function resend(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return new JsonResponse(['message' => __('auth.verification.already_verified')]);
        }

        $user->sendEmailVerificationNotification();

        return new JsonResponse(['message' => __('auth.verification.sent')]);
    }
}
