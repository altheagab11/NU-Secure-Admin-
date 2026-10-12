<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // NULL for date-range reports preserves their existing repeat-generation behavior.
        $scope = "CASE WHEN report_type = 'daily_visitor' THEN 0 WHEN report_type = 'daily_visitor_office' THEN COALESCE(office_id, 0) ELSE NULL END";

        $duplicates = DB::table('daily_reports')
            ->whereIn('report_type', ['daily_visitor', 'daily_visitor_office'])
            ->select('report_date', 'report_type')
            ->selectRaw($scope.' AS scope_id')
            ->groupBy('report_date', 'report_type')
            ->groupByRaw($scope)
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicates) {
            throw new RuntimeException('Duplicate daily report dates/scopes exist. Review and resolve those report records explicitly before migrating; no records have been deleted.');
        }

        Schema::table('daily_reports', function (Blueprint $table) use ($scope) {
            $column = $table->bigInteger('daily_scope_id')->nullable();
            if (DB::getDriverName() === 'sqlite') {
                $column->virtualAs($scope);
            } else {
                $column->storedAs($scope);
            }
            $table->unique(['report_date', 'report_type', 'daily_scope_id'], 'daily_reports_date_type_scope_unique');
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropUnique('daily_reports_date_type_scope_unique');
            $table->dropColumn('daily_scope_id');
        });
    }
};
