<?php

declare(strict_types=1);

namespace Tests\Feature\Cms;

use App\Models\Banner;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CmsAdminTest extends TestCase
{
    use RefreshDatabase;

    private function cmsAdmin(): User
    {
        Permission::findOrCreate('cms.manage', 'web');
        $admin = User::factory()->create();
        $admin->givePermissionTo('cms.manage');

        return $admin;
    }

    public function test_an_admin_can_create_a_page(): void
    {
        Sanctum::actingAs($this->cmsAdmin());

        $this->postJson('/api/v1/admin/pages', [
            'title' => 'Shipping Policy',
            'slug' => 'shipping-policy',
            'content' => '<p>Policy</p>',
            'is_published' => true,
        ])->assertCreated()->assertJsonPath('data.slug', 'shipping-policy');

        $this->assertDatabaseHas('pages', ['slug' => 'shipping-policy']);
    }

    public function test_creating_a_page_requires_permission(): void
    {
        Permission::findOrCreate('cms.manage', 'web');
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/admin/pages', ['title' => 'X', 'slug' => 'x'])
            ->assertForbidden();
    }

    public function test_an_admin_can_create_and_delete_a_banner(): void
    {
        Sanctum::actingAs($this->cmsAdmin());

        $this->postJson('/api/v1/admin/banners', [
            'title' => 'Hero',
            'image' => 'banners/hero.webp',
            'position' => 'hero',
        ])->assertCreated();

        $banner = Banner::query()->firstOrFail();

        $this->deleteJson("/api/v1/admin/banners/{$banner->id}")->assertOk();
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }

    public function test_an_admin_can_publish_a_blog_post(): void
    {
        Sanctum::actingAs($this->cmsAdmin());

        $this->postJson('/api/v1/admin/blog/posts', [
            'title' => 'New Arrivals',
            'slug' => 'new-arrivals',
            'content' => '<p>Hi</p>',
            'published_at' => now()->toDateTimeString(),
        ])->assertCreated();

        $this->assertDatabaseHas('blog_posts', ['slug' => 'new-arrivals']);
    }

    public function test_unpublished_pages_are_hidden_from_the_storefront(): void
    {
        Page::factory()->draft()->create(['slug' => 'hidden']);

        $this->getJson('/api/v1/pages/hidden')->assertNotFound();
    }
}
