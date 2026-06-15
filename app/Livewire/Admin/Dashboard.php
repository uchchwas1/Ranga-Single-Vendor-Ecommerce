<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Services\Reports\ReportService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Admin dashboard: KPI cards backed by the reporting service.
 */
class Dashboard extends Component
{
    /**
     * The dashboard KPI payload.
     *
     * @var array<string, mixed>
     */
    public array $kpis = [];

    /**
     * Load the KPIs when the component mounts.
     */
    public function mount(ReportService $reports): void
    {
        $this->kpis = $reports->dashboard();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.dashboard');
    }
}
