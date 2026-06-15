<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Cms;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\BlogPostResource;
use App\Models\BlogCategory;
use App\Services\Cms\BlogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Public blog browsing.
 */
class BlogController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly BlogService $blog,
    ) {
    }

    /**
     * GET /blog — published posts, optionally filtered by category.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $category = $request->query('category');

        return BlogPostResource::collection(
            $this->blog->publishedPosts(is_string($category) ? $category : null),
        );
    }

    /**
     * GET /blog/categories — blog categories.
     */
    public function categories(): JsonResponse
    {
        $categories = BlogCategory::query()->orderBy('name')->get()
            ->map(static fn (BlogCategory $c): array => ['name' => $c->name, 'slug' => $c->slug])
            ->all();

        return new JsonResponse(['data' => $categories]);
    }

    /**
     * GET /blog/{slug} — a single published post (records a view).
     */
    public function show(string $slug): BlogPostResource
    {
        $post = $this->blog->findAndView($slug);

        if ($post === null) {
            throw new NotFoundHttpException(__('cms.blog.not_found'));
        }

        return new BlogPostResource($post);
    }
}
