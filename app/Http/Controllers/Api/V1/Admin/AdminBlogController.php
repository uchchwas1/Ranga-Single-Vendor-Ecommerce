<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CreateBlogCategoryRequest;
use App\Http\Requests\Admin\CreateBlogPostRequest;
use App\Http\Resources\Cms\BlogPostResource;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Admin blog management (categories + posts).
 */
class AdminBlogController extends Controller
{
    /**
     * GET /admin/blog/posts — list all posts.
     */
    public function index(): AnonymousResourceCollection
    {
        return BlogPostResource::collection(
            BlogPost::query()->with(['category', 'author'])->latest()->paginate(20),
        );
    }

    /**
     * POST /admin/blog/categories — create a category.
     */
    public function storeCategory(CreateBlogCategoryRequest $request): JsonResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->validated();
        BlogCategory::query()->create($data);

        return new JsonResponse(['message' => __('cms.blog.category_created')], 201);
    }

    /**
     * POST /admin/blog/posts — create a post authored by the current admin.
     */
    public function storePost(CreateBlogPostRequest $request): JsonResponse
    {
        /** @var User $author */
        $author = $request->user();

        /** @var array<string, mixed> $data */
        $data = $request->validated();
        $data['user_id'] = $author->id;

        /** @var BlogPost $post */
        $post = BlogPost::query()->create($data);

        return (new BlogPostResource($post->load(['category', 'author'])))->response()->setStatusCode(201);
    }

    /**
     * DELETE /admin/blog/posts/{post} — delete a post.
     */
    public function destroyPost(BlogPost $post): JsonResponse
    {
        $post->delete();

        return new JsonResponse(['message' => __('cms.blog.post_deleted')]);
    }
}
