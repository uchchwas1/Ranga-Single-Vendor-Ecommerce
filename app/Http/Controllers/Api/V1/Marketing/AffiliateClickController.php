<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Marketing;

use App\Http\Controllers\Controller;
use App\Services\Marketing\AffiliateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Records affiliate referral clicks.
 */
class AffiliateClickController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly AffiliateService $affiliates,
    ) {
    }

    /**
     * GET /aff/{code} — record a referral click and return the code to
     * persist client-side for checkout attribution.
     */
    public function track(Request $request, string $code): JsonResponse
    {
        $affiliate = $this->affiliates->findActiveByCode($code);

        if ($affiliate === null) {
            throw new NotFoundHttpException(__('marketing.affiliate.not_found'));
        }

        $this->affiliates->recordClick(
            $affiliate,
            $request->ip(),
            $request->headers->get('referer'),
            (string) $request->query('landing', '/'),
        );

        return new JsonResponse(['affiliate_code' => $affiliate->code]);
    }
}
