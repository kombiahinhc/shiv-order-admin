<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(): JsonResponse
    {
        $shops = Shop::where('status', Shop::STATUS_APPROVED)
            ->orderBy('name')
            ->get(['id', 'name', 'owner', 'phone', 'address', 'status', 'gst_number', 'image_path', 'latitude', 'longitude']);

        return response()->json(['shops' => $shops]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'owner' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'gst_number' => ['nullable', 'string', 'max:50'],
            'image_path' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $shop = Shop::create([
            'name' => $data['name'],
            'owner' => $data['owner'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'status' => Shop::STATUS_PENDING,
            'requested_by' => $request->user()->id,
            'gst_number' => $data['gst_number'] ?? null,
            'image_path' => $data['image_path'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ]);

        return response()->json([
            'message' => 'Shop request submitted for approval.',
            'shop' => $shop,
        ], 201);
    }

    public function approve(Request $request, Shop $shop): JsonResponse
    {
        abort_if(! $request->user()->isAdminOrManager(), 403, 'Admin access required.');

        if ($shop->status === Shop::STATUS_APPROVED) {
            return response()->json(['message' => 'Shop already approved.', 'shop' => $shop]);
        }

        $shop->approve($request->user());

        return response()->json(['message' => 'Shop approved.', 'shop' => $shop]);
    }

    public function pending(Request $request): JsonResponse
    {
        abort_if(! $request->user()->isAdminOrManager(), 403, 'Admin access required.');

        $shops = Shop::where('status', Shop::STATUS_PENDING)
            ->with('requestedBy:id,name')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['shops' => $shops]);
    }

    public function salespeople(): JsonResponse
    {
        $people = User::where('role', User::ROLE_SALESREP)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone']);

        return response()->json(['salespeople' => $people]);
    }
}
