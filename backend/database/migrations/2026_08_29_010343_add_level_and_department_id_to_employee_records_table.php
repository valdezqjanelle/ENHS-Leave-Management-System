<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_records', function (Blueprint $table) {
            $table->string('level')->nullable()->after('employee_category');
            $table->foreignId('department_id')
                ->nullable()
                ->after('level')
                ->constrained('departments', 'department_id')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn('level');
        });
    }
};