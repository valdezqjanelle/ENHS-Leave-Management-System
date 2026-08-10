<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_applications', function (Blueprint $table) {

            $table->id('leave_id');

            // RELATIONSHIPS
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('leave_type_id');

            $table->foreign('employee_id')
                ->references('employee_id')
                ->on('employee_records')
                ->onDelete('cascade');

            $table->foreign('leave_type_id')
                ->references('leave_type_id')
                ->on('leave_types');

            // BASIC INFO
            $table->date('date_filed');

            // LEAVE TYPE SNAPSHOT (ENHS STRUCTURE)
            $table->enum('vacation_location_type', ['within_philippines', 'abroad'])->nullable();
            $table->string('vacation_location')->nullable();

            $table->enum('sick_type', ['in_hospital', 'out_patient'])->nullable();
            $table->string('illness')->nullable();

            // STUDY LEAVE
            $table->boolean('masters_degree')->default(false);
            $table->boolean('board_exam_review')->default(false);

            // OTHER PURPOSES
            $table->boolean('monetization')->default(false);
            $table->boolean('terminal_leave')->default(false);
            $table->string('other_purpose')->nullable();

            // DAYS INFO
            $table->integer('number_of_days');
            $table->date('start_date');
            $table->date('end_date');

            // COMMUTATION
            $table->enum('commutation', ['requested', 'not requested'])->nullable();

            // REASON
            $table->text('reason');

            // CERTIFICATION SNAPSHOT (ENHS PART 7A)
            $table->date('certification_as_of')->nullable();

            $table->decimal('vacation_total_earned', 8, 2)->nullable();
            $table->decimal('vacation_less_application', 8, 2)->nullable();
            $table->decimal('vacation_balance', 8, 2)->nullable();

            $table->decimal('sick_total_earned', 8, 2)->nullable();
            $table->decimal('sick_less_application', 8, 2)->nullable();
            $table->decimal('sick_balance', 8, 2)->nullable();

            // RECOMMENDATION
            $table->enum('recommendation_status', ['pending', 'approved', 'disapproved'])->default('pending');
            $table->text('recommendation_reason')->nullable();

            // APPROVAL
            $table->integer('days_with_pay')->nullable();
            $table->integer('days_without_pay')->nullable();
            $table->string('other_approval')->nullable();

            // FINAL STATUS
            $table->enum('final_status', ['pending', 'approved', 'disapproved'])->default('pending');

            $table->text('disapproval_reason')->nullable();
            $table->text('admin_remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_applications');
    }
};