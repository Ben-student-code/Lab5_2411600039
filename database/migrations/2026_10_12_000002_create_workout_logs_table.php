<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('workout_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action');
            $table->json('changes')->nullable();
            $table->string('workout_name')->nullable();
            $table->timestamps();

            $table->index(['workout_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_logs');
    }
};