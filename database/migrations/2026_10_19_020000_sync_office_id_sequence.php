<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::transaction(function () {
            // Imported office IDs do not advance PostgreSQL's identity sequence.
            // Block inserts while aligning it with existing directory records.
            DB::statement('LOCK TABLE office IN SHARE ROW EXCLUSIVE MODE');
            $sequence = DB::selectOne("SELECT pg_get_serial_sequence('office', 'office_id') AS name")->name;

            if ($sequence === null) {
                throw new RuntimeException('The office ID column has no automatic sequence.');
            }

            // Never rewind a sequence that has already advanced beyond the rows.
            DB::selectOne(
                'SELECT setval(CAST(? AS regclass), GREATEST(COALESCE((SELECT MAX(office_id) FROM office), 1), COALESCE(pg_sequence_last_value(CAST(? AS regclass)), 1)), true)',
                [$sequence, $sequence]
            );
        });
    }

    public function down(): void
    {
        // Rewinding the sequence would reintroduce duplicate primary keys.
    }
};
