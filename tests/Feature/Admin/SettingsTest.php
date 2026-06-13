<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    public function test_public_settings_endpoint_only_exposes_public_settings(): void
    {
        Setting::factory()->public()->create(['group' => 'general', 'key' => 'brand_name', 'value' => 'Ranga']);
        Setting::factory()->create(['group' => 'security', 'key' => 'secret_key', 'value' => 'hidden']);

        $this->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('data.general.brand_name', 'Ranga')
            ->assertJsonMissingPath('data.security');
    }

    public function test_an_admin_can_list_all_settings(): void
    {
        Setting::factory()->create(['group' => 'security', 'key' => 'secret_key', 'value' => 'hidden']);

        Sanctum::actingAs($this->admin());

        $this->getJson('/api/v1/admin/settings')
            ->assertOk()
            ->assertJsonPath('data.security.secret_key', 'hidden');
    }

    public function test_an_admin_can_upsert_settings(): void
    {
        Sanctum::actingAs($this->admin());

        $this->putJson('/api/v1/admin/settings', [
            'settings' => [
                ['group' => 'general', 'key' => 'brand_name', 'value' => 'Rebrand', 'is_public' => true],
                ['group' => 'general', 'key' => 'brand_color', 'value' => '#123456'],
            ],
        ])->assertOk();

        $this->assertDatabaseHas('settings', ['group' => 'general', 'key' => 'brand_name']);
        $this->assertSame('Rebrand', Setting::query()->where('key', 'brand_name')->firstOrFail()->value);
    }

    public function test_a_customer_cannot_manage_settings(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('customer');
        Sanctum::actingAs($customer);

        $this->getJson('/api/v1/admin/settings')->assertForbidden();

        $this->putJson('/api/v1/admin/settings', [
            'settings' => [['group' => 'general', 'key' => 'brand_name', 'value' => 'Hack']],
        ])->assertForbidden();
    }

    public function test_settings_management_requires_authentication(): void
    {
        $this->getJson('/api/v1/admin/settings')->assertUnauthorized();
        $this->putJson('/api/v1/admin/settings', [])->assertUnauthorized();
    }

    public function test_settings_upsert_validates_payload(): void
    {
        Sanctum::actingAs($this->admin());

        $this->putJson('/api/v1/admin/settings', ['settings' => 'not-an-array'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['settings']);
    }
}
