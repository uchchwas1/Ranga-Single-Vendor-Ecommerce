<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Marketing;

use App\Http\Controllers\Controller;
use App\Http\Resources\Marketing\BundleResource;
use App\Services\Marketing\BundleService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Public product-bundle browsing.
 */
class BundleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly BundleService $bundles,
    ) {
    }

    /**
     * GET /bundles — active bundles.
     */
    public function index(): AnonymousResourceCollection
    {
        return BundleResource::collection($this->bundles->active());
    }

    /**
     * GET /bundles/{slug} — a single active bundle.
     */
    public function show(string $slug): BundleResource
    {
        $bundle = $this->bundles->findBySlug($slug);

        if ($bundle === null) {
            throw new NotFoundHttpException(__('marketing.bundle.not_found'));
        }

        return new BundleResource($bundle);
    }
}
