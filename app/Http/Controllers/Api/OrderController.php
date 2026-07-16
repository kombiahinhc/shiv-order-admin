<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Shop;
use App\Services\OrderCalculator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function __construct(protected OrderCalculator $calculator)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request);

        $query = Order::with(['salesperson:id,name', 'shop:id,name', 'lines'])
            ->orderByDesc('order_date')
            ->orderByDesc('id');

        if ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', $request->integer('salesperson_id'));
        }
        if ($request->filled('shop_id')) {
            $query->where('shop_id', $request->integer('shop_id'));
        }
        if ($request->filled('from')) {
            $query->whereDate('order_date', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('order_date', '<=', $request->date('to'));
        }

        $perPage = min($request->integer('per_page', 50), 200);

        return response()->json($query->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'orders' => ['required', 'array'],
            'orders.*.local_uuid' => ['required', 'string'],
            'orders.*.shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'orders.*.shop_name_snapshot' => ['nullable', 'string'],
            'orders.*.order_date' => ['required', 'date'],
            'orders.*.notes' => ['nullable', 'string'],
            'orders.*.discount_type' => ['required', Rule::in(['none', 'percent', 'amount'])],
            'orders.*.discount_value' => ['required', 'numeric', 'min:0'],
            'orders.*.lines' => ['required', 'array', 'min:1'],
            'orders.*.lines.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'orders.*.lines.*.product_name' => ['required', 'string'],
            'orders.*.lines.*.unit' => ['nullable', 'string'],
            'orders.*.lines.*.qty' => ['required', 'numeric', 'min:0'],
            'orders.*.lines.*.unit_price' => ['required', 'numeric', 'min:0'],
            'orders.*.lines.*.discount' => ['nullable', 'numeric', 'min:0'],
            'orders.*.lines.*.tax_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $results = [];
        $salespersonId = $request->user()->id;

        DB::transaction(function () use ($payload, $salespersonId, &$results) {
            foreach ($payload['orders'] as $orderData) {
                $lines = $orderData['lines'];
                $totals = $this->calculator->calculate(
                    $lines,
                    $orderData['discount_type'],
                    $orderData['discount_value']
                );

                $order = Order::updateOrCreate(
                    [
                        'local_uuid' => $orderData['local_uuid'],
                        'salesperson_id' => $salespersonId,
                    ],
                    [
                        'shop_id' => $orderData['shop_id'] ?? null,
                        'shop_name_snapshot' => $orderData['shop_name_snapshot'] ?? null,
                        'order_date' => $orderData['order_date'],
                        'notes' => $orderData['notes'] ?? null,
                        'discount_type' => $orderData['discount_type'],
                        'discount_value' => $orderData['discount_value'],
                        'subtotal' => $totals['subtotal'],
                        'tax_total' => $totals['tax_total'],
                        'grand_total' => $totals['grand_total'],
                        'sync_status' => Order::SYNC_SYNCED,
                        'synced_at' => now(),
                    ]
                );

                $order->lines()->delete();
                foreach ($lines as $line) {
                    $order->lines()->create([
                        'product_id' => $line['product_id'] ?? null,
                        'product_name' => $line['product_name'],
                        'unit' => $line['unit'] ?? null,
                        'qty' => $line['qty'],
                        'unit_price' => $line['unit_price'],
                        'discount' => $line['discount'] ?? 0,
                        'tax_rate' => $line['tax_rate'] ?? 0,
                        'line_total' => max(0, (float) $line['qty'] * (float) $line['unit_price'] - (float) ($line['discount'] ?? 0)),
                    ]);
                }

                $results[] = [
                    'local_uuid' => $order->local_uuid,
                    'id' => $order->id,
                    'sync_status' => $order->sync_status,
                ];
            }
        });

        return response()->json(['synced' => $results], 201);
    }

    public function myOrders(Request $request): JsonResponse
    {
        $salespersonId = $request->user()->id;

        $orders = Order::with('lines')
            ->where('salesperson_id', $salespersonId)
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->get();

        return response()->json(['orders' => $orders]);
    }

    protected function authorizeAdmin(Request $request): void
    {
        abort_if(! $request->user()->isAdminOrManager(), 403, 'Admin access required.');
    }
}
