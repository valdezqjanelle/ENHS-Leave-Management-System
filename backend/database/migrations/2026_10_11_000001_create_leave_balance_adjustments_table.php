<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('leave_balance_adjustments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('admin_user_id');
            $table->decimal('vacation_before', 12, 3);
            $table->decimal('vacation_after', 12, 3);
            $table->decimal('sick_before', 12, 3);
            $table->decimal('sick_after', 12, 3);
            $table->string('reason', 500);
            $table->timestamps();
            $table->foreign('employee_id')->references('employee_id')->on('employee_records');
            $table->foreign('admin_user_id')->references('user_id')->on('users');
            $table->index(['employee_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balance_adjustments');
    }
};
