<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('non_teaching_personnel_records', function (Blueprint $table) {
            $table->id('non_teaching_record_id');

            $table->unsignedBigInteger('employee_id');

            $table->string('office_assignment');
            $table->string('job_assignment')->nullable();
            $table->text('job_description')->nullable();

            $table->timestamps();

            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employee_records')
                ->onDelete('cascade');

            $table->unique('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('non_teaching_personnel_records');
    }
};