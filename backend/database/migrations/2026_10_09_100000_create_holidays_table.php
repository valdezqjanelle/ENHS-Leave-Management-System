<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            $table->enum('type', [
                'Regular Holiday',
                'Special Non-Working Day',
                'School Non-Working Day',
                'Other Non-Working Day',
            ]);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['date', 'name']);
            $table->index(['date', 'is_active']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holidays');
    }
};
