<?php

require __DIR__.'/vendor/autoload.php';

$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = rtrim((string) env('SUPABASE_URL', ''), '/');
$key = (string) (env('SUPABASE_SERVICE_ROLE_KEY') ?: env('SUPABASE_KEY') ?: '');

$headers = [
    'apikey' => $key,
    'Authorization' => 'Bearer '.$key,
    'Accept' => 'application/json',
];

$visitId = 24;

$response = Illuminate\Support\Facades\Http::withHeaders($headers)
    ->timeout(20)
    ->get($baseUrl.'/rest/v1/office_expectation', [
        'select' => 'visit_id,expected_order,arrived_at,office(office_name),expectation_status(status_name)',
        'visit_id' => 'eq.'.$visitId,
    ]);

$payload = $response->json();

echo 'REST status: '.$response->status().PHP_EOL;
echo 'REST rows: '.(is_array($payload) ? count($payload) : -1).PHP_EOL;

echo 'REST body:'.PHP_EOL;
echo $response->body().PHP_EOL;

$dbRows = Illuminate\Support\Facades\DB::table('office_expectation')
    ->where('visit_id', $visitId)
    ->count();

echo 'DB rows: '.$dbRows.PHP_EOL;
