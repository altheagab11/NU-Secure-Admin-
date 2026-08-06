<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('office_scan') || ! Schema::hasColumn('office_scan', 'office_id')) {
            return;
        }

        // Allow facility exit scans without an office destination (e.g. contractors).
        DB::statement('ALTER TABLE office_scan ALTER COLUMN office_id DROP NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('office_scan') || ! Schema::hasColumn('office_scan', 'office_id')) {
            return;
        }

        DB::table('office_scan')->whereNull('office_id')->update([
            'office_id' => DB::raw('(SELECT MIN(office_id) FROM office)'),
        ]);

        DB::statement('ALTER TABLE office_scan ALTER COLUMN office_id SET NOT NULL');
    }
};
