<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            // Add user_id — nullable because existing rows have no user
            $table->foreignId('user_id')
                  ->nullable()
                  ->after('id')
                  ->constrained()
                  ->onDelete('cascade');

            // Track when a reminder was sent to avoid duplicate notifications
            $table->timestamp('reminder_sent_at')
                  ->nullable()
                  ->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'reminder_sent_at']);
        });
    }
};