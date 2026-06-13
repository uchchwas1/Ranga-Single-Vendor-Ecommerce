<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalogue;

use App\Http\Controllers\Controller;
use App\Http\Requests\Catalogue\SearchRequest;
use App\Http\Resources\Catalogue\ProductResource;
use App\Services\Search\SearchService;
use App\Support\Dto\ProductFilters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Full-text catalogue search endpoints.
 */
class SearchController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SearchService $search,
    ) {
    }

    /**
     * GET /search — paginated full-text product search.
     */
    public function index(SearchRequest $request): AnonymousResourceCollection
    {
        $filters = ProductFilters::fromArray($request->validated());

        $results = $this->search->search((string) $request->string('q'), $filters);

        return ProductResource::collection($results);
    }

    /**
     * GET /search/suggestions — instant suggestions for the search bar.
     */
    public function suggestions(SearchRequest $request): JsonResponse
    {
        $suggestions = $this->search->suggestions((string) $request->string('q'));

        return new JsonResponse(['data' => $suggestions]);
    }
}
