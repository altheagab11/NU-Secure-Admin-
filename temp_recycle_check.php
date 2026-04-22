<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$rows = DB::table('office_staff as s')
    ->leftJoin('users as u', 'u.user_id', '=', 's.user_id')
    ->leftJoin('office as o', 'o.office_id', '=', 's.office_id')
    ->where('u.status', 'recycle_bin')
    ->select([
        'u.user_id',
        'u.first_name',
        'u.last_name',
        'u.email',
        's.position',
        DB::raw("COALESCE(o.office_name, '—') as office_name"),
    ])
    ->orderBy('u.email')
    ->get();

echo 'recycled_rows=' . $rows->count() . PHP_EOL;
echo json_encode($rows, JSON_PRETTY_PRINT) . PHP_EOL;
