<?php

use App\Models\Order;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/orders/export', function () {
    $orders = Order::with(['salesperson', 'shop', 'lines'])->orderByDesc('order_date')->get();

    $headers = ['Order Date', 'Sales Rep', 'Shop', 'Items', 'Subtotal', 'Tax', 'Grand Total'];
    $rows = [$headers];

    foreach ($orders as $order) {
        $rows[] = [
            $order->order_date,
            $order->salesperson?->name ?? '',
            $order->shop?->name ?? $order->shop_name_snapshot ?? '',
            $order->lines->count(),
            $order->subtotal,
            $order->tax_total,
            $order->grand_total,
        ];
    }

    $csv = '';
    foreach ($rows as $row) {
        $csv .= implode(',', array_map(fn ($v) => '"'.str_replace('"', '""', $v).'"', $row))."\n";
    }

    return response($csv, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="orders.csv"',
    ]);
})->name('orders.export');
