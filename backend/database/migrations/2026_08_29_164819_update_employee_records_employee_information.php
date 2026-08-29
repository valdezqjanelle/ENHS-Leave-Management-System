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
    Schema::table('employee_records', function (Blueprint $table) {
        $table->string('extension_name')->nullable()->after('last_name');
        $table->date('date_of_birth')->nullable()->after('extension_name');
        $table->string('civil_status')->nullable()->after('date_of_birth');
        $table->string('nationality')->nullable()->after('civil_status');
        $table->text('address')->nullable()->after('nationality');
        $table->string('personal_email')->nullable()->after('address');
        $table->string('emergency_contact_name')->nullable()->after('personal_email');
        $table->string('emergency_contact_number')->nullable()->after('emergency_contact_name');

        $table->string('personnel_type')->nullable()->after('level');
        $table->string('employment_category')->nullable()->after('employment_status');

        $table->unsignedBigInteger('supervisor_id')->nullable()->after('position_id');

        $table->foreign('supervisor_id')
            ->references('employee_id')
            ->on('employee_records')
            ->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('employee_records', function (Blueprint $table) {
        $table->dropForeign(['supervisor_id']);

        $table->dropColumn([
            'extension_name',
            'date_of_birth',
            'civil_status',
            'nationality',
            'address',
            'personal_email',
            'emergency_contact_name',
            'emergency_contact_number',
            'personnel_type',
            'employment_category',
            'supervisor_id',
        ]);
    });
}
};
