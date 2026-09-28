<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('visit')) {
            return;
        }

        Schema::table('visit', function (Blueprint $table) {
            if (! Schema::hasColumn('visit', 'incomplete_route_reviewed')) {
                $table->boolean('incomplete_route_reviewed')->default(false);
            }

            if (! Schema::hasColumn('visit', 'incomplete_route_note')) {
                $table->text('incomplete_route_note')->nullable();
            }

            if (! Schema::hasColumn('visit', 'incomplete_route_reviewed_by')) {
                $table->integer('incomplete_route_reviewed_by')->nullable();
            }
        });

        if (
            Schema::hasColumn('visit', 'incomplete_route_reviewed_by')
            && Schema::hasTable('users')
            && Schema::hasColumn('users', 'user_id')
            && ! $this->constraintExists('visit', 'visit_incomplete_route_reviewed_by_fkey')
        ) {
            DB::statement('
                ALTER TABLE visit
                ADD CONSTRAINT visit_incomplete_route_reviewed_by_fkey
                FOREIGN KEY (incomplete_route_reviewed_by) REFERENCES users(user_id)
            ');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('visit')) {
            return;
        }

        $this->dropConstraintIfExists('visit', 'visit_incomplete_route_reviewed_by_fkey');

        Schema::table('visit', function (Blueprint $table) {
            $columns = collect([
                'incomplete_route_reviewed',
                'incomplete_route_note',
                'incomplete_route_reviewed_by',
            ])->filter(fn (string $column) => Schema::hasColumn('visit', $column))->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
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
