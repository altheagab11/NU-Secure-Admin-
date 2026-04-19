<?php

require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$cols = Illuminate\Support\Facades\DB::select(<<<'SQL'
select column_name
from information_schema.columns
where table_schema = 'public'
  and table_name = 'office_expectation'
order by ordinal_position
SQL);

$fks = Illuminate\Support\Facades\DB::select(<<<'SQL'
select tc.constraint_name,
       kcu.column_name,
       ccu.table_name as foreign_table,
       ccu.column_name as foreign_column
from information_schema.table_constraints tc
join information_schema.key_column_usage kcu
  on tc.constraint_name = kcu.constraint_name
 and tc.table_schema = kcu.table_schema
join information_schema.constraint_column_usage ccu
  on ccu.constraint_name = tc.constraint_name
 and ccu.table_schema = tc.table_schema
where tc.table_schema = 'public'
  and tc.table_name = 'office_expectation'
  and tc.constraint_type = 'FOREIGN KEY'
order by tc.constraint_name, kcu.ordinal_position
SQL);

echo "COLUMNS\n";
echo json_encode($cols, JSON_PRETTY_PRINT) . PHP_EOL;

echo "FKS\n";
echo json_encode($fks, JSON_PRETTY_PRINT) . PHP_EOL;
