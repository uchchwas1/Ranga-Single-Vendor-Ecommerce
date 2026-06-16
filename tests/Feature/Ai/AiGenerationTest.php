<?php

declare(strict_types=1);

namespace Tests\Feature\Ai;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AiGenerationTest extends TestCase
{
    use RefreshDatabase;

    private function fakeOpenAi(string $content): void
    {
        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [['message' => ['content' => $content]]],
            ]),
        ]);
    }

    private function adminWith(string ...$permissions): User
    {
        $admin = User::factory()->create();

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
            $admin->givePermissionTo($permission);
        }

        return $admin;
    }

    public function test_an_admin_can_generate_a_product_description(): void
    {
        $this->fakeOpenAi('<p>A beautiful crimson saree.</p>');
        Sanctum::actingAs($this->adminWith('products.manage'));

        $this->postJson('/api/v1/admin/ai/product-description', [
            'name' => 'Crimson Saree',
            'category' => 'Sarees',
        ])
            ->assertOk()
            ->assertJsonPath('description', '<p>A beautiful crimson saree.</p>');
    }

    public function test_seo_meta_generation_parses_json(): void
    {
        $this->fakeOpenAi('{"title":"Crimson Saree","description":"Shop the crimson saree.","keywords":["saree","crimson"]}');
        Sanctum::actingAs($this->adminWith('seo.manage'));

        $this->postJson('/api/v1/admin/ai/seo-meta', ['context' => 'Crimson Saree product'])
            ->assertOk()
            ->assertJsonPath('data.title', 'Crimson Saree')
            ->assertJsonPath('data.keywords.0', 'saree');
    }

    public function test_tag_generation_returns_a_list(): void
    {
        $this->fakeOpenAi('saree, crimson, ethnic, লাল');
        Sanctum::actingAs($this->adminWith('products.manage'));

        $this->postJson('/api/v1/admin/ai/tags', ['description' => 'A crimson silk saree'])
            ->assertOk()
            ->assertJsonCount(4, 'tags')
            ->assertJsonPath('tags.0', 'saree');
    }

    public function test_ai_generation_requires_permission(): void
    {
        Permission::findOrCreate('products.manage', 'web');
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/admin/ai/product-description', ['name' => 'X'])
            ->assertForbidden();
    }
}
