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
        $table->string('position')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('employee_records', function (Blueprint $table) {
        $table->string('position')->nullable(false)->change();
    });
}
};
