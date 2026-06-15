<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Dashboard;
use App\Models\Order;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardLivewireTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_dashboard_component_loads_kpis(): void
    {
        $customer = User::factory()->create();
        Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Confirmed, 'total' => 2000]);

        $component = Livewire::test(Dashboard::class)->assertOk();

        /** @var array<string, mixed> $kpis */
        $kpis = $component->get('kpis');
        $this->assertArrayHasKey('sales_30d', $kpis);
        $this->assertArrayHasKey('customers', $kpis);
    }
}
