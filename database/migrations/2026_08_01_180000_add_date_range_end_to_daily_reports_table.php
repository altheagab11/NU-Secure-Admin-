<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->date('date_range_end')->nullable()->after('report_date');
        });

        // Daily reports remain unique per calendar date + type.
        // Date-range reports may share a start date with different ends / regenerations,
        // so uniqueness is enforced in application logic for daily_visitor only.
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropUnique('daily_reports_date_type_unique');
            $table->index(['report_date', 'report_type'], 'daily_reports_date_type_index');
            $table->index(['report_type', 'date_range_end'], 'daily_reports_type_range_end_index');
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropIndex('daily_reports_date_type_index');
            $table->dropIndex('daily_reports_type_range_end_index');
            $table->unique(['report_date', 'report_type'], 'daily_reports_date_type_unique');
            $table->dropColumn('date_range_end');
        });
    }
};
