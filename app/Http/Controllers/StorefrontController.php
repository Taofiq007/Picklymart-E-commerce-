<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:191'],
        ]);

        $search = trim($filters['q'] ?? '');
        $categorySlug = $filters['category'] ?? null;

        $categories = Category::query()
            ->where('status', 'active')
            ->withCount(['products' => fn (Builder $query) => $query->where('status', 'active')])
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->where('status', 'active')
            ->with([
                'category',
                'mediaAssets' => fn (BelongsToMany $query) => $query
                    ->where('media_assets.status', 'active')
                    ->orderByPivot('position'),
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($categorySlug, fn (Builder $query) => $query->whereHas(
                'category',
                function (Builder $categoryQuery) use ($categorySlug): void {
                    $categoryQuery->where('status', 'active')->where(function (Builder $query) use ($categorySlug): void {
                        $query->where('slug', $categorySlug)
                            ->orWhereHas('parent', fn (Builder $parentQuery) => $parentQuery
                                ->where('slug', $categorySlug)
                                ->where('status', 'active'));
                    });
                },
            ))
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $products->through(function (Product $product): Product {
            $product->setAttribute(
                'storefront_image',
                $product->mediaAssets->first()?->publicUrl(),
            );

            return $product;
        });

        return view('storefront.index', compact('categories', 'products', 'search', 'categorySlug'));
    }
}
