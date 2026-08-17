<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            Schema::hasTable('guard_duty_shifts')
            && Schema::hasColumn('guard_duty_shifts', 'guard_user_id')
            && Schema::hasTable('users')
            && Schema::hasColumn('users', 'user_id')
            && ! $this->constraintExists('guard_duty_shifts', 'guard_duty_shifts_guard_user_id_fkey')
        ) {
            DB::statement('
                ALTER TABLE guard_duty_shifts
                ADD CONSTRAINT guard_duty_shifts_guard_user_id_fkey
                FOREIGN KEY (guard_user_id) REFERENCES users(user_id)
            ');
        }

        if (
            Schema::hasTable('visit')
            && Schema::hasColumn('visit', 'on_duty_guard_id')
            && Schema::hasTable('users')
            && Schema::hasColumn('users', 'user_id')
            && ! $this->constraintExists('visit', 'visit_on_duty_guard_id_fkey')
        ) {
            DB::statement('
                ALTER TABLE visit
                ADD CONSTRAINT visit_on_duty_guard_id_fkey
                FOREIGN KEY (on_duty_guard_id) REFERENCES users(user_id)
            ');
        }

        if (
            Schema::hasTable('visit')
            && Schema::hasColumn('visit', 'duty_shift_id')
            && Schema::hasTable('guard_duty_shifts')
            && Schema::hasColumn('guard_duty_shifts', 'shift_id')
            && ! $this->constraintExists('visit', 'visit_duty_shift_id_fkey')
        ) {
            DB::statement('
                ALTER TABLE visit
                ADD CONSTRAINT visit_duty_shift_id_fkey
                FOREIGN KEY (duty_shift_id) REFERENCES guard_duty_shifts(shift_id)
            ');
        }

        if (
            Schema::hasTable('guard_duty_shifts')
            && Schema::hasColumn('guard_duty_shifts', 'kiosk_user_id')
            && Schema::hasColumn('guard_duty_shifts', 'clock_out_at')
            && ! $this->indexExists('one_active_guard_per_kiosk')
        ) {
            DB::statement('
                CREATE UNIQUE INDEX one_active_guard_per_kiosk
                ON guard_duty_shifts (kiosk_user_id)
                WHERE clock_out_at IS NULL
            ');
        }
    }

    public function down(): void
    {
        if ($this->indexExists('one_active_guard_per_kiosk')) {
            DB::statement('DROP INDEX IF EXISTS one_active_guard_per_kiosk');
        }

        $this->dropConstraintIfExists('visit', 'visit_duty_shift_id_fkey');
        $this->dropConstraintIfExists('visit', 'visit_on_duty_guard_id_fkey');
        $this->dropConstraintIfExists('guard_duty_shifts', 'guard_duty_shifts_guard_user_id_fkey');
    }

    private function constraintExists(string $table, string $constraint): bool
    {
        $row = DB::selectOne(
            "select 1 as present
             from information_schema.table_constraints
             where table_schema = 'public'
               and table_name = ?
               and constraint_name = ?",
            [$table, $constraint]
        );

        return $row !== null;
    }

    private function indexExists(string $indexName): bool
    {
        $row = DB::selectOne(
            'select 1 as present
             from pg_indexes
             where schemaname = \'public\'
               and indexname = ?',
            [$indexName]
        );

        return $row !== null;
    }

    private function dropConstraintIfExists(string $table, string $constraint): void
    {
        if (! Schema::hasTable($table) || ! $this->constraintExists($table, $constraint)) {
            return;
        }

        DB::statement('ALTER TABLE '.$table.' DROP CONSTRAINT IF EXISTS '.$constraint);
    }
};
