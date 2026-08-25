<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('positions', function (Blueprint $table) {
        $table->id();
        $table->string('code')->unique();
        $table->string('name');
        $table->string('type'); // Teaching, Non-Teaching, School Head
        $table->string('salary_grade')->nullable(); // reference only, some positions have none
        $table->timestamps();
    });
}

    public function down(): void
{
    Schema::dropIfExists('positions');
}
};
