<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('salary_grade');
            $table->unsignedInteger('step');
            $table->decimal('salary', 10, 2);
            $table->timestamps();

            $table->unique(['salary_grade', 'step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_schedules');
    }
};