<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Ai\GenerateDescriptionRequest;
use App\Http\Requests\Ai\GenerateSeoRequest;
use App\Http\Requests\Ai\GenerateTagsRequest;
use App\Services\AI\ProductDescriptionService;
use App\Services\AI\ProductTagService;
use App\Services\AI\SeoMetaGeneratorService;
use Illuminate\Http\JsonResponse;

/**
 * Admin AI content-generation endpoints.
 */
class AdminAiController extends Controller
{
    /**
     * POST /admin/ai/product-description — generate a product description.
     */
    public function productDescription(GenerateDescriptionRequest $request, ProductDescriptionService $service): JsonResponse
    {
        /** @var array<string, mixed> $attributes */
        $attributes = $request->validated('attributes') ?? [];

        $description = $service->generate(
            (string) $request->validated('name'),
            $request->validated('category'),
            $attributes,
        );

        return new JsonResponse(['description' => $description]);
    }

    /**
     * POST /admin/ai/seo-meta — generate SEO metadata.
     */
    public function seoMeta(GenerateSeoRequest $request, SeoMetaGeneratorService $service): JsonResponse
    {
        return new JsonResponse(['data' => $service->generate((string) $request->validated('context'))]);
    }

    /**
     * POST /admin/ai/tags — generate product tags.
     */
    public function tags(GenerateTagsRequest $request, ProductTagService $service): JsonResponse
    {
        $tags = $service->generate(
            (string) $request->validated('description'),
            $request->validated('category'),
        );

        return new JsonResponse(['tags' => $tags]);
    }
}
