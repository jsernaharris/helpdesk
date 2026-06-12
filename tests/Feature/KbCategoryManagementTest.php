<?php

namespace Tests\Feature;

use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Organization;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KbCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
        $org = Organization::create(['name' => 'MSP', 'slug' => 'msp', 'is_msp' => true, 'is_active' => true]);
        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@msp.test', 'password' => bcrypt('secret'),
            'organization_id' => $org->id, 'is_active' => true,
        ]);
        $this->admin->assignRole('msp_admin');
    }

    public function test_admin_can_create_category_with_auto_slug(): void
    {
        $this->actingAs($this->admin)->post(route('staff.kb-categories.store'), [
            'name' => 'Networking Guides',
        ])->assertRedirect();

        $this->assertDatabaseHas('kb_categories', [
            'name' => 'Networking Guides', 'slug' => 'networking-guides',
        ]);
    }

    public function test_duplicate_name_gets_unique_slug(): void
    {
        KbCategory::create(['name' => 'General', 'slug' => 'general', 'is_active' => true]);

        $this->actingAs($this->admin)->post(route('staff.kb-categories.store'), [
            'name' => 'General',
        ])->assertRedirect();

        $this->assertDatabaseHas('kb_categories', ['slug' => 'general-2']);
    }

    public function test_category_with_articles_cannot_be_deleted(): void
    {
        $category = KbCategory::create(['name' => 'Email', 'slug' => 'email', 'is_active' => true]);
        KbArticle::create([
            'category_id' => $category->id, 'author_id' => $this->admin->id,
            'title' => 'Setup', 'slug' => 'setup', 'content' => 'x',
            'visibility' => 'public', 'status' => 'published',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('staff.kb-categories.destroy', $category))
            ->assertRedirect();

        $this->assertDatabaseHas('kb_categories', ['id' => $category->id]);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $category = KbCategory::create(['name' => 'Temp', 'slug' => 'temp', 'is_active' => true]);

        $this->actingAs($this->admin)
            ->delete(route('staff.kb-categories.destroy', $category))
            ->assertRedirect();

        $this->assertDatabaseMissing('kb_categories', ['id' => $category->id]);
    }
}
