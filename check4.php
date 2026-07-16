<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$shops = App\Models\Shop::where('status', 'approved')->get();
echo "Count: " . $shops->count() . PHP_EOL;
echo "First shop toJson: " . $shops->first()->toJson() . PHP_EOL;
echo "Collection toJson (first 500 chars): " . substr($shops->toJson(), 0, 500) . PHP_EOL;
