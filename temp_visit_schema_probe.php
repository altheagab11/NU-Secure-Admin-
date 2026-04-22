<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = Illuminate\Support\Facades\DB::select(<<<'SQL'
select column_name, data_type
from information_schema.columns
where table_schema = 'public'
  and table_name = 'visit'
order by ordinal_position
SQL);

foreach ($columns as $column) {
    echo $column->column_name . '|' . $column->data_type . PHP_EOL;
}
