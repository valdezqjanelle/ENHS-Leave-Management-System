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
        Schema::create('employee_records', function (Blueprint $table) {
            $table->id('employee_id');

            $table->unsignedBigInteger('user_id');

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');

            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('sex');
            $table->string('department');
            $table->string('position');
            $table->string('employee_category');
            $table->decimal('salary', 10, 2)->nullable();
            $table->string('contact_number')->nullable();
            $table->string('employment_status');
            $table->date('date_hired');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_records');
    }
};
