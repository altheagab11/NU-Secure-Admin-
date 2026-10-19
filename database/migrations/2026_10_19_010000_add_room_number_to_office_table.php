<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office', function (Blueprint $table) {
            $table->string('room_number', 30)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('office', function (Blueprint $table) {
            $table->dropColumn('room_number');
        });
    }
};
