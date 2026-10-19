<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'products' => Product::count(),
                'categories' => Category::count(),
                'brands' => Brand::count(),
                'assets' => MediaAsset::count(),
                'missingAssets' => MediaAsset::where('status', 'missing')->count(),
            ],
            'recentProducts' => Product::with('category')->latest()->limit(6)->get(),
        ]);
    }
}
