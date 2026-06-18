<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Catalogue;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ProductRepositoryContract;
use App\Services\Media\Contracts\QrGenerator;
use App\Services\Media\ShareService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Product QR code and social-share endpoints.
 */
class ProductMediaController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ProductRepositoryContract $products,
        private readonly QrGenerator $qr,
        private readonly ShareService $share,
    ) {
    }

    /**
     * GET /products/{slug}/qr — downloadable QR PNG linking to the product.
     */
    public function qr(string $slug): Response
    {
        $product = $this->resolve($slug);
        $png = $this->qr->png(url('/products/'.$product->slug));

        return new Response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="'.$product->slug.'-qr.png"',
        ]);
    }

    /**
     * GET /products/{slug}/share — a WhatsApp share deep link.
     */
    public function share(string $slug): JsonResponse
    {
        $product = $this->resolve($slug);

        return new JsonResponse(['whatsapp' => $this->share->product($product)]);
    }

    /**
     * Resolve an active product by slug or 404.
     */
    private function resolve(string $slug): \App\Models\Product
    {
        $product = $this->products->findActiveBySlug($slug);

        if ($product === null) {
            throw new NotFoundHttpException(__('catalogue.product_not_found'));
        }

        return $product;
    }
}
