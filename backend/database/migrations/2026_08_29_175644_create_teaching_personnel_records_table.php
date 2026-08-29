<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::create('teaching_personnel_records', function (Blueprint $table) {
        $table->id('teaching_record_id');

        $table->unsignedBigInteger('employee_id');

        $table->string('subject_specialization');
        $table->string('grade_level_handled');
        $table->string('advisory_class')->nullable();
        $table->text('teaching_load')->nullable();
        $table->decimal('teaching_hours', 5, 2)->nullable();

        $table->timestamps();

        $table->foreign('employee_id')
            ->references('employee_id')
            ->on('employee_records')
            ->onDelete('cascade');

        $table->unique('employee_id');
    });
}
};
