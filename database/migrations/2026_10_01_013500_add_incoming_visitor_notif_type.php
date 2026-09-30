<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notif_type')) {
            return;
        }

        $exists = DB::table('notif_type')
            ->whereRaw('LOWER(TRIM(COALESCE(notif_type_name, \'\'))) = ?', ['incoming visitor'])
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('notif_type')->insert([
            'notif_type_name' => 'Incoming Visitor',
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('notif_type')) {
            return;
        }

        DB::table('notif_type')
            ->whereRaw('LOWER(TRIM(COALESCE(notif_type_name, \'\'))) = ?', ['incoming visitor'])
            ->delete();
    }
};
