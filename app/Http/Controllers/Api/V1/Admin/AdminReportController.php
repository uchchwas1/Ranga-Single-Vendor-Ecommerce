<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Services\Reports\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Admin reporting endpoints.
 */
class AdminReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly ReportService $reports,
    ) {
    }

    /**
     * GET /admin/reports/dashboard — headline KPIs.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $this->ensureCanView($request);

        return new JsonResponse(['data' => $this->reports->dashboard()]);
    }

    /**
     * GET /admin/reports/sales — sales summary + top products for a range.
     */
    public function sales(Request $request): JsonResponse
    {
        $this->ensureCanView($request);
        [$from, $to] = $this->range($request);

        return new JsonResponse([
            'data' => [
                'range' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
                'summary' => $this->reports->salesSummary($from, $to),
                'top_products' => $this->reports->topProducts($from, $to),
            ],
        ]);
    }

    /**
     * GET /admin/reports/customers — customer base summary.
     */
    public function customers(Request $request): JsonResponse
    {
        $this->ensureCanView($request);

        return new JsonResponse(['data' => $this->reports->customerSummary()]);
    }

    /**
     * GET /admin/reports/inventory — inventory health summary.
     */
    public function inventory(Request $request): JsonResponse
    {
        $this->ensureCanView($request);

        return new JsonResponse(['data' => $this->reports->inventorySummary()]);
    }

    /**
     * Ensure the actor may view reports.
     */
    private function ensureCanView(Request $request): void
    {
        abort_unless($request->user()?->can('reports.view') ?? false, 403);
    }

    /**
     * Resolve a [from, to] date range from the request (defaults to 30 days).
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function range(Request $request): array
    {
        $from = $request->date('from') ?? Carbon::now()->subDays(30);
        $to = $request->date('to') ?? Carbon::now();

        return [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()];
    }
}
