<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_displays_active_products_from_the_catalog(): void
    {
        $category = Category::create([
            'name' => 'Mobile Phones',
            'slug' => 'mobile-phones',
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Demo Smartphone',
            'slug' => 'demo-smartphone',
            'category_id' => $category->id,
            'price' => 12000,
            'sale_price' => 10999,
            'currency' => 'BDT',
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Hidden Draft',
            'slug' => 'hidden-draft',
            'status' => 'draft',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Everyday finds')
            ->assertSee('Demo Smartphone')
            ->assertSee('Mobile Phones')
            ->assertSee('৳10,999')
            ->assertDontSee('Hidden Draft');
    }

    public function test_storefront_search_filters_catalog_products(): void
    {
        Product::create([
            'name' => 'Searchable Phone',
            'slug' => 'searchable-phone',
            'sku' => 'PHONE-001',
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Other Product',
            'slug' => 'other-product',
            'status' => 'active',
        ]);

        $this->get('/?q=PHONE-001')
            ->assertOk()
            ->assertSee('Searchable Phone')
            ->assertDontSee('Other Product');
    }

    public function test_parent_category_includes_products_in_its_subcategories(): void
    {
        $parent = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => 'active',
        ]);

        $child = Category::create([
            'name' => 'Mobile Phones',
            'slug' => 'mobile-phones',
            'parent_id' => $parent->id,
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Catalog Phone',
            'slug' => 'catalog-phone',
            'category_id' => $child->id,
            'status' => 'active',
        ]);

        $this->get('/?category=electronics')
            ->assertOk()
            ->assertSee('Catalog Phone');
    }
}
