<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_records', function (Blueprint $table) {

            $table->id('attendance_id');

            $table->unsignedBigInteger('employee_id');

            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employee_records')
                ->onDelete('cascade');

            $table->date('attendance_date');

            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();

            $table->enum('status', [
                'Present',
                'Late',
                'Absent',
                'Half Day',
                'On Leave'
            ]);


            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};