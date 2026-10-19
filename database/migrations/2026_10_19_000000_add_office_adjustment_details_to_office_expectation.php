<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_expectation', function (Blueprint $table) {
            $table->integer('added_by_user_id')->nullable();
            $table->text('addition_reason')->nullable();
            $table->foreign('added_by_user_id')->references('user_id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('office_expectation', function (Blueprint $table) {
            $table->dropForeign(['added_by_user_id']);
            $table->dropColumn(['added_by_user_id', 'addition_reason']);
        });
    }
};
