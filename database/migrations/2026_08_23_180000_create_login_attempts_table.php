<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('login_attempts')) {
            return;
        }

        Schema::create('login_attempts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->string('email')->nullable();
            $table->string('role', 30)->nullable();
            $table->string('status', 20);
            $table->string('failure_reason', 100)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('login_source', 20)->nullable();
            $table->timestamp('attempted_at');
            $table->timestamps();

            $table->index('user_id', 'idx_login_attempts_user_id');
            $table->index('email', 'idx_login_attempts_email');
            $table->index('role', 'idx_login_attempts_role');
            $table->index('status', 'idx_login_attempts_status');
            $table->index('attempted_at', 'idx_login_attempts_attempted_at');
        });

        $usersPk = Schema::hasColumn('users', 'user_id')
            ? 'user_id'
            : (Schema::hasColumn('users', 'id') ? 'id' : null);

        if ($usersPk !== null) {
            Schema::table('login_attempts', function (Blueprint $table) use ($usersPk) {
                $table->foreign('user_id', 'login_attempts_user_id_fkey')
                    ->references($usersPk)
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
