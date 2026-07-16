<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "shops: " . App\Models\Shop::count() . PHP_EOL;
echo "products: " . App\Models\Product::count() . PHP_EOL;
echo "orders: " . App\Models\Order::count() . PHP_EOL;
echo "statuses:" . PHP_EOL;
foreach (App\Models\Shop::select('status')->distinct()->get() as $s) {
    echo "  - " . $s->status . PHP_EOL;
}
echo "approved shops:" . PHP_EOL;
foreach (App\Models\Shop::where('status', 'approved')->get() as $s) {
    echo "  - " . $s->id . " | " . $s->name . " | " . $s->status . PHP_EOL;
}
