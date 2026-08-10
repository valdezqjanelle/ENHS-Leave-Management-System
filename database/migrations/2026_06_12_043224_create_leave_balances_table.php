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
    Schema::create('leave_balances', function (Blueprint $table) {

        $table->bigIncrements('balance_id');

        $table->unsignedBigInteger('employee_id')->unique();

        $table->foreign('employee_id')
            ->references('employee_id')
            ->on('employee_records')
            ->onDelete('cascade');
        // Leave earnings
        $table->decimal('vacation_earned', 8, 2)->default(0);
        $table->decimal('sick_earned', 8, 2)->default(0);

        // Leave balances
        $table->decimal('vacation_balance', 8, 2)->default(0);
        $table->decimal('sick_balance', 8, 2)->default(0);

        // tracking
        $table->timestamp('last_updated')->nullable();
        $table->decimal('used_leave', 8, 2)->default(0);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }

    
};
