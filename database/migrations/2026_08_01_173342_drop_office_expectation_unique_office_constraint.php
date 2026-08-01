<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enrollee route revisits Admissions Office at step 9 (welcome kit).
     * The old unique (visit_id, office_id) blocked that second expectation row.
     */
    public function up(): void
    {
        if (! Schema::hasTable('office_expectation')) {
            return;
        }

        DB::statement('ALTER TABLE office_expectation DROP CONSTRAINT IF EXISTS office_expectation_unique_office');

        $this->backfillMissingFinalAdmissionsStep();
    }

    public function down(): void
    {
        if (! Schema::hasTable('office_expectation')) {
            return;
        }

        // Remove duplicate office rows (keep lowest expected_order) before restoring unique.
        DB::statement(<<<'SQL'
            DELETE FROM office_expectation oe
            USING office_expectation newer
            WHERE oe.visit_id = newer.visit_id
              AND oe.office_id = newer.office_id
              AND oe.expectation_id > newer.expectation_id
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE office_expectation
            ADD CONSTRAINT office_expectation_unique_office UNIQUE (visit_id, office_id)
        SQL);
    }

    protected function backfillMissingFinalAdmissionsStep(): void
    {
        $admissionsOfficeId = (int) DB::table('enrollee_step')
            ->where('is_active', true)
            ->where('step_order', 9)
            ->value('office_id');

        if ($admissionsOfficeId <= 0) {
            $admissionsOfficeId = (int) DB::table('office')
                ->whereRaw("LOWER(office_name) LIKE '%admission%'")
                ->value('office_id');
        }

        if ($admissionsOfficeId <= 0) {
            return;
        }

        $pendingStatusId = DB::table('expectation_status')
            ->whereRaw("LOWER(TRIM(COALESCE(status_name, ''))) IN ('pending', 'not arrived', 'awaiting', 'scheduled', 'expected', 'open')")
            ->orderBy('expectation_status_id')
            ->value('expectation_status_id');

        $visitIds = DB::table('office_expectation as oe')
            ->join('visit as v', 'v.visit_id', '=', 'oe.visit_id')
            ->leftJoin('visit_type as vt', 'vt.visit_type_id', '=', 'v.visit_type_id')
            ->where(function ($query) {
                $query->whereRaw("LOWER(TRIM(COALESCE(vt.visit_type_name, ''))) = ?", ['enrollee'])
                    ->orWhereRaw("LOWER(TRIM(COALESCE(v.purpose_reason, ''))) LIKE ?", ['%enrollment%'])
                    ->orWhereExists(function ($exists) {
                        $exists->select(DB::raw(1))
                            ->from('enrollee as e')
                            ->whereColumn('e.visitor_id', 'v.visitor_id');
                    });
            })
            ->groupBy('oe.visit_id')
            ->havingRaw('COUNT(*) = 8')
            ->havingRaw('MAX(oe.expected_order) = 8')
            ->pluck('oe.visit_id');

        $now = now();

        foreach ($visitIds as $visitId) {
            $first = DB::table('office_expectation')
                ->where('visit_id', $visitId)
                ->where('expected_order', 1)
                ->first();

            if (! $first || (int) $first->office_id !== $admissionsOfficeId) {
                continue;
            }

            if (DB::table('office_expectation')->where('visit_id', $visitId)->where('expected_order', 9)->exists()) {
                continue;
            }

            DB::table('office_expectation')->insert([
                'visit_id' => (int) $visitId,
                'office_id' => $admissionsOfficeId,
                'expected_order' => 9,
                'expectation_status_id' => $pendingStatusId,
                'arrived_at' => null,
                'created_at' => $now,
            ]);
        }
    }
};
