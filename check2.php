<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$k = $app->make(Illuminate\Contracts\Console\Kernel::class);
$k->bootstrap();

$rows = Illuminate\Support\Facades\DB::table('shops')->select('id','name','status')->get();
foreach ($rows as $r) {
    echo $r->id . ' | ' . $r->name . ' | ' . $r->status . PHP_EOL;
}

$json = Illuminate\Support\Facades\DB::table('shops')->where('status','approved')->get();
echo "API-like JSON count: " . $json->count() . PHP_EOL;
echo "First shop JSON: " . $json->first()->name . PHP_EOL;
