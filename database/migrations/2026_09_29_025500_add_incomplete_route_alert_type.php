<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('alerts') || ! Schema::hasColumn('alerts', 'alert_type')) {
            return;
        }

        $this->dropAlertTypeChecks();

        DB::statement("
            ALTER TABLE alerts
            ADD CONSTRAINT alerts_alert_type_check
            CHECK (alert_type::text = ANY (ARRAY[
                'Wrong Office'::character varying,
                'Unauthorized'::character varying,
                'Overstay'::character varying,
                'Suspicious'::character varying,
                'Incomplete Route'::character varying
            ]::text[]))
        ");
    }

    public function down(): void
    {
        if (! Schema::hasTable('alerts') || ! Schema::hasColumn('alerts', 'alert_type')) {
            return;
        }

        DB::table('alerts')
            ->where('alert_type', 'Incomplete Route')
            ->update(['alert_type' => 'Suspicious']);

        $this->dropAlertTypeChecks();

        DB::statement("
            ALTER TABLE alerts
            ADD CONSTRAINT alerts_alert_type_check
            CHECK (alert_type::text = ANY (ARRAY[
                'Wrong Office'::character varying,
                'Unauthorized'::character varying,
                'Overstay'::character varying,
                'Suspicious'::character varying
            ]::text[]))
        ");
    }

    private function dropAlertTypeChecks(): void
    {
        $constraints = DB::select("
            select con.conname
            from pg_constraint con
            join pg_class rel on rel.oid = con.conrelid
            join pg_namespace nsp on nsp.oid = rel.relnamespace
            where nsp.nspname = 'public'
              and rel.relname = 'alerts'
              and con.contype = 'c'
              and pg_get_constraintdef(con.oid) ilike '%alert_type%'
        ");

        foreach ($constraints as $constraint) {
            $name = (string) ($constraint->conname ?? '');
            if ($name === '') {
                continue;
            }

            DB::statement('ALTER TABLE alerts DROP CONSTRAINT IF EXISTS '.$name);
        }
    }
};
