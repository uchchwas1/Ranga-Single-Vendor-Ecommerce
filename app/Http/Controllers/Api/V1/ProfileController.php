<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Http\Request;

/**
 * Read and update the authenticated user's profile.
 */
class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly UserRepositoryContract $users,
    ) {
    }

    /**
     * Show the authenticated profile.
     */
    public function show(Request $request): UserResource
    {
        /** @var User $user */
        $user = $request->user();

        return new UserResource($user);
    }

    /**
     * Update the authenticated profile.
     */
    public function update(UpdateProfileRequest $request): UserResource
    {
        /** @var User $user */
        $user = $request->user();

        $this->authorize('update', $user);

        $updated = $this->users->update($user, $request->validated());

        return new UserResource($updated);
    }
}
