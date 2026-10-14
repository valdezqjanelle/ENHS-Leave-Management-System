<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salary_schedule_rates', function (Blueprint $table) {
            $table->decimal('salary', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('salary_schedule_rates')->whereNull('salary')->exists()) {
            throw new \RuntimeException('Complete or remove incomplete draft rates before rolling back this migration.');
        }
        Schema::table('salary_schedule_rates', function (Blueprint $table) {
            $table->decimal('salary', 10, 2)->nullable(false)->change();
        });
    }
};
