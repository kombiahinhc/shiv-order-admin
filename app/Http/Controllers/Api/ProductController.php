<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'category', 'unit', 'list_price', 'tax_rate']);

        return response()->json(['products' => $products]);
    }
}
