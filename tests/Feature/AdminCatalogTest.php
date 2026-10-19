<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_routes_redirect_guests_to_the_sign_in_page(): void
    {
        $this->get('/admin')
            ->assertRedirect(route('login'));

        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign in to admin');
    }

    public function test_non_admin_users_cannot_open_admin_or_catalog_api_routes(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->get('/admin')->assertForbidden();
        $this->getJson('/api/v1/products')->assertForbidden();
    }

    public function test_non_admin_users_cannot_sign_in_to_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_sign_in_and_reach_the_catalog_dashboard(): void
    {
        $admin = $this->createAdmin();

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password',
        ])
            ->assertRedirect(route('admin.dashboard'));

        $this->get('/admin')->assertOk()->assertSee('Workspace overview');
    }

    public function test_admin_can_create_a_parent_category_and_circular_moves_are_rejected(): void
    {
        $this->actingAs($this->createAdmin());

        $this->post(route('admin.categories.store'), [
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ])->assertRedirect();

        $parent = Category::where('slug', 'electronics')->firstOrFail();
        $child = Category::create([
            'name' => 'Mobile Phones',
            'slug' => 'mobile-phones',
            'parent_id' => $parent->id,
            'status' => 'active',
        ]);

        $this->put(route('admin.categories.update', $parent), [
            'name' => $parent->name,
            'slug' => $parent->slug,
            'parent_id' => $child->id,
            'status' => 'active',
        ])->assertSessionHasErrors('parent_id');

        $this->assertDatabaseHas('categories', [
            'id' => $parent->id,
            'parent_id' => null,
        ]);
    }

    public function test_admin_category_cycle_check_terminates_when_existing_data_already_contains_a_cycle(): void
    {
        $this->actingAs($this->createAdmin());
        $first = Category::create(['name' => 'First', 'slug' => 'first', 'status' => 'active']);
        $second = Category::create(['name' => 'Second', 'slug' => 'second', 'status' => 'active']);
        $third = Category::create(['name' => 'Third', 'slug' => 'third', 'status' => 'active']);
        DB::table('categories')->where('id', $first->id)->update(['parent_id' => $second->id]);
        DB::table('categories')->where('id', $second->id)->update(['parent_id' => $first->id]);

        $this->put(route('admin.categories.update', $third), [
            'name' => $third->name,
            'slug' => $third->slug,
            'parent_id' => $first->id,
            'status' => 'active',
        ])->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'id' => $third->id,
            'parent_id' => $first->id,
        ]);
    }

    public function test_admin_can_create_products_and_map_active_media_assets(): void
    {
        $this->actingAs($this->createAdmin());
        Storage::fake('public');
        Storage::disk('public')->put('media/test-product.png', 'test image contents');

        $category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);
        $asset = MediaAsset::create([
            'filename' => 'test-product.png',
            'disk' => 'public',
            'path' => 'media/test-product.png',
            'mime_type' => 'image/png',
            'status' => 'active',
        ]);

        $this->post(route('admin.products.store'), [
            'name' => 'Sample Tablet',
            'sku' => 'TABLET-001',
            'category_id' => $category->id,
            'price' => '12000.00',
            'sale_price' => '11000.00',
            'currency' => 'BDT',
            'status' => 'active',
            'media_asset_ids' => [$asset->id],
        ])->assertRedirect();

        $product = Product::where('sku', 'TABLET-001')->firstOrFail();
        $this->assertDatabaseHas('product_media', [
            'product_id' => $product->id,
            'media_asset_id' => $asset->id,
            'role' => 'main',
            'position' => 0,
        ]);
    }

    public function test_admin_product_list_renders_prices_without_a_nested_ternary_error(): void
    {
        $this->actingAs($this->createAdmin());

        Product::create([
            'name' => 'Priced product',
            'slug' => 'priced-product',
            'price' => 1200,
            'sale_price' => 900,
            'status' => 'active',
        ]);
        Product::create([
            'name' => 'Free product',
            'slug' => 'free-product',
            'price' => 0,
            'status' => 'active',
        ]);
        Product::create([
            'name' => 'Unpriced product',
            'slug' => 'unpriced-product',
            'status' => 'draft',
        ]);

        $this->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('Priced product')
            ->assertSee('৳900.00')
            ->assertSee('Free product')
            ->assertSee('৳0.00')
            ->assertSee('Unpriced product')
            ->assertSee('—');
    }

    public function test_admin_can_upload_repository_media_and_map_it_to_a_category(): void
    {
        $this->actingAs($this->createAdmin());
        Storage::fake('public');
        $category = Category::create([
            'name' => 'Home',
            'slug' => 'home',
            'status' => 'active',
        ]);

        $this->post(route('admin.media-assets.store'), [
            'source_id' => 'central-home-banner',
            'file' => UploadedFile::fake()->image('home-banner.png'),
        ])->assertRedirect(route('admin.media-assets.index'));

        $asset = MediaAsset::where('source_id', 'central-home-banner')->firstOrFail();
        Storage::disk('public')->assertExists($asset->path);

        $this->post(route('admin.media-assets.categories.attach', $asset), [
            'category_id' => $category->id,
            'role' => 'banner',
            'position' => 0,
        ])->assertRedirect(route('admin.media-assets.index'));

        $this->assertDatabaseHas('category_media', [
            'category_id' => $category->id,
            'media_asset_id' => $asset->id,
            'role' => 'banner',
        ]);
    }

    public function test_admin_can_register_an_existing_image_path_from_a_configured_disk(): void
    {
        $this->actingAs($this->createAdmin());
        Storage::fake('public');
        $image = UploadedFile::fake()->image('central-image.png');
        Storage::disk('public')->put('central/catalog/central-image.png', file_get_contents($image->getPathname()));

        $this->post(route('admin.media-assets.store'), [
            'source_id' => 'central-image-001',
            'disk' => 'public',
            'path' => 'central/catalog/central-image.png',
        ])->assertRedirect(route('admin.media-assets.index'));

        $this->assertDatabaseHas('media_assets', [
            'source_id' => 'central-image-001',
            'disk' => 'public',
            'path' => 'central/catalog/central-image.png',
            'mime_type' => 'image/png',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_register_an_existing_central_image_path_through_the_api(): void
    {
        Sanctum::actingAs($this->createAdmin());
        Storage::fake('public');
        $image = UploadedFile::fake()->image('banner.png');
        Storage::disk('public')->put('central/catalog/banner.png', file_get_contents($image->getPathname()));

        $this->postJson('/api/v1/media-assets', [
            'source_id' => 'central-banner-001',
            'disk' => 'public',
            'path' => 'central/catalog/banner.png',
        ])
            ->assertCreated()
            ->assertJsonPath('data.source_id', 'central-banner-001')
            ->assertJsonPath('data.path', 'central/catalog/banner.png');

        $this->assertDatabaseHas('media_assets', [
            'source_id' => 'central-banner-001',
            'disk' => 'public',
            'path' => 'central/catalog/banner.png',
            'status' => 'active',
        ]);
    }

    public function test_api_rejects_missing_and_unsafe_existing_media_paths(): void
    {
        Sanctum::actingAs($this->createAdmin());
        Storage::fake('public');

        $this->postJson('/api/v1/media-assets', [
            'disk' => 'public',
            'path' => 'central/missing.png',
        ])->assertUnprocessable()->assertJsonValidationErrors('path');

        $this->postJson('/api/v1/media-assets', [
            'disk' => 'public',
            'path' => '../outside.png',
        ])->assertUnprocessable()->assertJsonValidationErrors('path');

        $this->assertDatabaseCount('media_assets', 0);
    }

    public function test_api_rejects_a_non_image_file_registered_as_media(): void
    {
        Sanctum::actingAs($this->createAdmin());
        Storage::fake('public');
        Storage::disk('public')->put('central/catalog/not-an-image.png', 'plain text');

        $this->postJson('/api/v1/media-assets', [
            'disk' => 'public',
            'path' => 'central/catalog/not-an-image.png',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('path');

        $this->assertDatabaseCount('media_assets', 0);
    }

    public function test_admin_can_issue_a_token_that_authenticates_catalog_api_requests(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $this->post(route('admin.integrations.tokens.store'), [
            'name' => 'Local catalog sync',
        ])
            ->assertRedirect(route('admin.integrations.index'))
            ->assertSessionHas('new_api_token');

        $plainTextToken = $this->app['session.store']->get('new_api_token');
        $this->assertIsString($plainTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $admin->id,
            'name' => 'Local catalog sync',
        ]);

        $this->withToken($plainTextToken)
            ->getJson('/api/v1/products')
            ->assertOk();
    }

    public function test_admin_can_view_media_registration_and_api_integration_instructions(): void
    {
        $this->actingAs($this->createAdmin());

        $this->get(route('admin.media-assets.index'))
            ->assertOk()
            ->assertSee('Register existing repository file')
            ->assertSee('Repository-relative path');

        $this->get(route('admin.integrations.index'))
            ->assertOk()
            ->assertSee('Authorization: Bearer <ADMIN_TOKEN>')
            ->assertSee('POST JSON to');
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }
}
