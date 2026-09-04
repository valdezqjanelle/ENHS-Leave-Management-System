<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id('grade_level_id');
            $table->string('grade_name');
            $table->string('level', 20);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['grade_name', 'level']);
        });

        Schema::create('school_sections', function (Blueprint $table) {
            $table->id('section_id');
            $table->foreignId('grade_level_id')->constrained('grade_levels', 'grade_level_id')->cascadeOnDelete();
            $table->string('section_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['grade_level_id', 'section_name']);
        });

        Schema::create('teaching_subjects', function (Blueprint $table) {
            $table->id('subject_id');
            $table->foreignId('department_id')->nullable()->constrained('departments', 'department_id')->nullOnDelete();
            $table->string('subject_name');
            $table->string('level', 20);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['department_id', 'subject_name', 'level']);
        });

        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->foreignId('teaching_record_id')->constrained('teaching_personnel_records', 'teaching_record_id')->cascadeOnDelete();
            $table->foreignId('grade_level_id')->constrained('grade_levels', 'grade_level_id');
            $table->foreignId('section_id')->nullable()->constrained('school_sections', 'section_id')->nullOnDelete();
            $table->foreignId('subject_id')->constrained('teaching_subjects', 'subject_id');
            $table->string('school_year', 20);
            $table->boolean('is_advisory')->default(false);
            $table->decimal('teaching_hours', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['teaching_record_id', 'grade_level_id', 'section_id', 'subject_id', 'school_year'], 'teaching_assignment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_assignments');
        Schema::dropIfExists('teaching_subjects');
        Schema::dropIfExists('school_sections');
        Schema::dropIfExists('grade_levels');
    }
};
