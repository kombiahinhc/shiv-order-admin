<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$shop = App\Models\Shop::first();
echo "DB status: " . $shop->status . PHP_EOL;
echo "toJson: " . $shop->toJson() . PHP_EOL;
