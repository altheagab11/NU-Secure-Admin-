<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->integer('user_id')->nullable();
            $table->string('action', 100);
            $table->string('module', 100);
            $table->text('description');
            $table->string('entity_type', 100)->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->jsonb('old_values')->nullable();
            $table->jsonb('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('request_method', 10)->nullable();
            $table->text('request_url')->nullable();
            $table->string('status', 30)->default('Success');
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id', 'idx_activity_logs_user_id');
            $table->index('module', 'idx_activity_logs_module');
            $table->index('action', 'idx_activity_logs_action');
            $table->index('created_at', 'idx_activity_logs_created_at');
            $table->index('status', 'idx_activity_logs_status');
        });

        $usersPk = Schema::hasColumn('users', 'user_id')
            ? 'user_id'
            : (Schema::hasColumn('users', 'id') ? 'id' : null);

        if ($usersPk !== null) {
            Schema::table('activity_logs', function (Blueprint $table) use ($usersPk) {
                $table->foreign('user_id', 'activity_logs_user_id_fkey')
                    ->references($usersPk)
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
