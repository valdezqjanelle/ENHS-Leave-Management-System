<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('positions', fn(Blueprint $table) => $table->boolean('is_active')->default(true));
        Schema::create('salary_management_control', fn(Blueprint $table) => $table->unsignedInteger('id')->primary());
        DB::table('salary_management_control')->insert(['id' => 1]);
        Schema::create('salary_schedule_versions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->date('effective_date')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('revision')->default(1);
            $table->string('source_reference', 500)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->foreignId('activated_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
        Schema::create('salary_schedule_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('salary_schedule_versions')->cascadeOnDelete();
            $table->unsignedInteger('salary_grade');
            $table->unsignedInteger('step');
            $table->decimal('salary', 10, 2);
            $table->unique(['schedule_id', 'salary_grade', 'step']);
        });
        $id = DB::table('salary_schedule_versions')->insertGetId([
            'name' => 'Imported existing schedule',
            'effective_date' => null,
            'status' => 'active',
            'revision' => 1,
            'source_reference' => 'Imported from existing database rates. Original effective date was not recorded.',
            'activated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $rows = DB::table('salary_schedules')->get(['salary_grade', 'step', 'salary']);
        foreach ($rows->chunk(250) as $chunk) {
            DB::table('salary_schedule_rates')->insert($chunk->map(fn($rate) => [
                'schedule_id' => $id,
                'salary_grade' => $rate->salary_grade,
                'step' => $rate->step,
                'salary' => $rate->salary,
            ])->all());
        }
        // Do not change existing employee/admin salary amounts or seeders' rates.
    }
    public function down(): void
    {
        throw new \RuntimeException('Compensation management contains retained salary history. Restore a reviewed backup instead of rolling back this migration.');
    }
};
