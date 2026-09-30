<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leave_year_control', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary();
        });
        DB::table('leave_year_control')->insert(['id' => 1]);
        Schema::create('leave_school_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('vl_grant', 12, 3);
            $table->decimal('sl_grant', 12, 3);
            $table->unsignedBigInteger('processed_by');
            $table->timestamp('processed_at');
        });
        Schema::create('leave_school_year_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained('leave_school_years');
            $table->unsignedBigInteger('employee_id');
            $table->string('employee_name');
            $table->boolean('processed');
            $table->json('before_balances');
            $table->json('after_balances');
            $table->text('review_note')->nullable();
            $table->unique(['school_year_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_school_year_entries');
        Schema::dropIfExists('leave_school_years');
        Schema::dropIfExists('leave_year_control');
    }
};
