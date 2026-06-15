<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertSeoMetaRequest;
use App\Models\Brand;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use App\Services\Seo\SeoService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Admin SEO metadata management.
 */
class AdminSeoController extends Controller
{
    /**
     * Friendly model-type keys to their classes.
     *
     * @var array<string, class-string<Model>>
     */
    private const MODELS = [
        'product' => Product::class,
        'category' => Category::class,
        'brand' => Brand::class,
        'page' => Page::class,
        'blog_post' => BlogPost::class,
    ];

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SeoService $seo,
    ) {
    }

    /**
     * PUT /admin/seo — create or update SEO metadata for a model.
     */
    public function upsert(UpsertSeoMetaRequest $request): JsonResponse
    {
        $class = self::MODELS[(string) $request->validated('model_type')] ?? null;

        if ($class === null) {
            throw new NotFoundHttpException(__('cms.seo.unknown_type'));
        }

        $model = $class::query()->find($request->validated('model_id'));

        if ($model === null) {
            throw new NotFoundHttpException(__('cms.seo.model_not_found'));
        }

        /** @var array<string, mixed> $data */
        $data = $request->safe()->except(['model_type', 'model_id']);

        $meta = $this->seo->upsertFor($model, $data);

        return new JsonResponse([
            'message' => __('cms.seo.saved'),
            'data' => [
                'title' => $meta->title,
                'description' => $meta->description,
                'canonical_url' => $meta->canonical_url,
            ],
        ]);
    }
}
