<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'category', 'unit', 'list_price', 'tax_rate']);

        return response()->json(['products' => $products]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_if(! $request->user()->isAdminOrManager(), 403, 'Admin access required.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:20'],
            'list_price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $product = Product::create([
            'name' => $data['name'],
            'sku' => $data['sku'] ?? null,
            'category' => $data['category'] ?? null,
            'unit' => $data['unit'] ?? null,
            'list_price' => $data['list_price'],
            'tax_rate' => $data['tax_rate'] ?? 0,
            'active' => true,
        ]);

        return response()->json([
            'message' => 'Product created.',
            'product' => $product,
        ], 201);
    }
}
