<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('guard_personnel')) {
            Schema::create('guard_personnel', function (Blueprint $table) {
                $table->id('guard_personnel_id');
                $table->string('first_name');
                $table->string('middle_name')->nullable();
                $table->string('last_name');
                $table->string('badge_number')->unique();
                $table->string('duty_pin');
                $table->string('status', 32)->default('active');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('guard_duty_shifts')) {
            Schema::table('guard_duty_shifts', function (Blueprint $table) {
                if (! Schema::hasColumn('guard_duty_shifts', 'guard_personnel_id')) {
                    $table->unsignedBigInteger('guard_personnel_id')->nullable()->after('guard_user_id');
                }

                if (! Schema::hasColumn('guard_duty_shifts', 'station')) {
                    $table->string('station')->nullable()->after('kiosk_user_id');
                }
            });

            if (
                Schema::hasColumn('guard_duty_shifts', 'guard_personnel_id')
                && ! $this->constraintExists('guard_duty_shifts', 'guard_duty_shifts_guard_personnel_id_fkey')
            ) {
                DB::statement('
                    ALTER TABLE guard_duty_shifts
                    ADD CONSTRAINT guard_duty_shifts_guard_personnel_id_fkey
                    FOREIGN KEY (guard_personnel_id) REFERENCES guard_personnel(guard_personnel_id)
                ');
            }

            if (Schema::hasColumn('guard_duty_shifts', 'guard_user_id')) {
                try {
                    DB::statement('ALTER TABLE guard_duty_shifts ALTER COLUMN guard_user_id DROP NOT NULL');
                } catch (\Throwable $e) {
                    // Column may already be nullable.
                }
            }
        }
    }

    public function down(): void
    {
        $this->dropConstraintIfExists('guard_duty_shifts', 'guard_duty_shifts_guard_personnel_id_fkey');

        if (Schema::hasTable('guard_duty_shifts')) {
            Schema::table('guard_duty_shifts', function (Blueprint $table) {
                if (Schema::hasColumn('guard_duty_shifts', 'station')) {
                    $table->dropColumn('station');
                }

                if (Schema::hasColumn('guard_duty_shifts', 'guard_personnel_id')) {
                    $table->dropColumn('guard_personnel_id');
                }
            });
        }

        Schema::dropIfExists('guard_personnel');
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

    private function dropConstraintIfExists(string $table, string $constraint): void
    {
        if (! Schema::hasTable($table) || ! $this->constraintExists($table, $constraint)) {
            return;
        }

        DB::statement('ALTER TABLE '.$table.' DROP CONSTRAINT IF EXISTS '.$constraint);
    }
};
