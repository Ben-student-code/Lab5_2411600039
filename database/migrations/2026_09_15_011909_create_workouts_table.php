<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workouts', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->string('activity', 100);
            $table->enum('type', ['cardio', 'strength', 'flexibility', 'hiit']);
            $table->enum('intensity', ['low', 'medium', 'high']);
            $table->enum('status', ['Completed', 'Ongoing', 'Failed'])->default('Ongoing');
            $table->unsignedInteger('calories')->default(0);
            $table->unsignedInteger('duration')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('intensity');
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workouts');
    }
};