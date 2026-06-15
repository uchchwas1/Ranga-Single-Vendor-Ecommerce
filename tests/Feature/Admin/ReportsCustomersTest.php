<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use App\Support\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ReportsCustomersTest extends TestCase
{
    use RefreshDatabase;

    private function adminWith(string ...$permissions): User
    {
        $admin = User::factory()->create();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    public function test_the_dashboard_report_returns_kpis(): void
    {
        $customer = User::factory()->create();
        Order::factory()->create(['user_id' => $customer->id, 'status' => OrderStatus::Confirmed, 'total' => 1500]);

        Sanctum::actingAs($this->adminWith('reports.view'));

        $this->getJson('/api/v1/admin/reports/dashboard')
            ->assertOk()
            ->assertJsonStructure(['data' => ['sales_30d' => ['orders', 'gross_sales'], 'customers', 'inventory', 'top_products']]);
    }

    public function test_reports_require_permission(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/v1/admin/reports/dashboard')->assertForbidden();
    }

    public function test_an_admin_can_list_and_view_customers(): void
    {
        $admin = $this->adminWith('customers.view', 'customers.manage');
        $customer = User::factory()->create(['name' => 'Anika Rahman']);

        Sanctum::actingAs($admin);

        $this->getJson('/api/v1/admin/customers?search=Anika')
            ->assertOk()
            ->assertJsonPath('data.0.name', 'Anika Rahman');

        $this->getJson("/api/v1/admin/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $customer->id);
    }

    public function test_an_admin_can_adjust_customer_loyalty_points(): void
    {
        $admin = $this->adminWith('customers.manage');
        $customer = User::factory()->create(['loyalty_points' => 100]);

        Sanctum::actingAs($admin);

        $this->postJson("/api/v1/admin/customers/{$customer->id}/loyalty", [
            'points' => 50,
            'note' => 'Goodwill',
        ])->assertOk()->assertJsonPath('balance', 150);

        $this->assertSame(150, $customer->fresh()?->loyalty_points);
        $this->assertDatabaseHas('loyalty_transactions', ['user_id' => $customer->id, 'type' => 'adjust']);
    }
}
