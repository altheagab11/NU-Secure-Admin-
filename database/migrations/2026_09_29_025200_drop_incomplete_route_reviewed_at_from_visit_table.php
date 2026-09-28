<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('visit') || ! Schema::hasColumn('visit', 'incomplete_route_reviewed_at')) {
            return;
        }

        Schema::table('visit', function (Blueprint $table) {
            $table->dropColumn('incomplete_route_reviewed_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('visit') || Schema::hasColumn('visit', 'incomplete_route_reviewed_at')) {
            return;
        }

        Schema::table('visit', function (Blueprint $table) {
            $table->timestamp('incomplete_route_reviewed_at')->nullable();
        });
    }
};
