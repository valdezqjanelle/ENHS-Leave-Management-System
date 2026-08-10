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
        Schema::create('leave_credits', function (Blueprint $table) {
    $table->id('credits_id');

    $table->unsignedBigInteger('employee_id');

    $table->foreign('employee_id')
          ->references('employee_id')
          ->on('employee_records');

    $table->string('activity_name');
    $table->decimal('hours_rendered', 8, 2);
    $table->decimal('equivalent_leave_days', 8, 2);
$table->enum('credit_type', ['Vacation', 'Sick']);

$table->enum('status', ['Pending', 'Applied'])
      ->default('Pending');
    $table->date('date_recorded');
    $table->unsignedBigInteger('recorded_by');
    $table->foreign('recorded_by')->references('user_id')->on('users');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_credits');
    }
};
