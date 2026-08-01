<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->string('report_type', 64)->default('daily_visitor');
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedInteger('record_count')->default(0);
            $table->string('generation_status', 32)->default('pending');
            $table->timestamp('generated_at')->nullable();
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(['report_date', 'report_type'], 'daily_reports_date_type_unique');
            $table->index('generation_status');
            $table->index('generated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
