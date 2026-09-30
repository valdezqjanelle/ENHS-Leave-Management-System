<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_time_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('unit', 10);
            $table->unsignedSmallInteger('quantity');
            $table->decimal('equivalent_days', 10, 3);
            $table->unique(['unit', 'quantity']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('work_time_conversions');
    }
};
