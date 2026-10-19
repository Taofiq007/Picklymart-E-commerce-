<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PicklymartCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_endpoint_returns_paginated_resource_data(): void
    {
        Sanctum::actingAs($this->createAdmin());

        $category = Category::create([
            'source_id' => 'cat-test', 'name' => 'Test Category', 'slug' => 'test-category', 'status' => 'active',
        ]);
        Product::create([
            'source_id' => 'product-test', 'sku' => 'SKU-TEST', 'slug' => 'test-product',
            'name' => 'Test Product', 'category_id' => $category->id, 'status' => 'active',
        ]);

        $this->getJson('/api/v1/products?search=SKU-TEST')
            ->assertOk()
            ->assertJsonPath('data.0.sku', 'SKU-TEST')
            ->assertJsonPath('data.0.category.name', 'Test Category');
    }

    public function test_media_asset_mapping_can_be_attached_to_a_product(): void
    {
        Sanctum::actingAs($this->createAdmin());

        Storage::fake('public');
        Storage::disk('public')->put('media/test.png', 'not-an-image-but-present-for-storage-test');
        $product = Product::create([
            'source_id' => 'product-map-test', 'sku' => 'SKU-MAP', 'slug' => 'mapping-product',
            'name' => 'Mapping Product', 'status' => 'active',
        ]);
        $asset = MediaAsset::create([
            'source_id' => 'asset-map-test', 'filename' => 'test.png', 'disk' => 'public',
            'path' => 'media/test.png', 'mime_type' => 'image/png', 'status' => 'active',
        ]);

        $this->postJson("/api/v1/products/{$product->id}/media", [
            'media_asset_id' => $asset->id, 'role' => 'main', 'position' => 0,
        ])->assertOk();

        $this->assertDatabaseHas('product_media', [
            'product_id' => $product->id, 'media_asset_id' => $asset->id, 'role' => 'main', 'position' => 0,
        ]);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->get('/api/v1/products')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Unauthenticated.']);
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }
}
