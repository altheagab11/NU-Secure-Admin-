<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('office_id')->nullable()->after('report_type');
            $table->index(['office_id', 'report_type', 'report_date'], 'daily_reports_office_type_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropIndex('daily_reports_office_type_date_index');
            $table->dropColumn('office_id');
        });
    }
};
