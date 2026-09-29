<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('employee_records', function (Blueprint $table) {
        $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_number');
    });
}

public function down()
{
    Schema::table('employee_records', function (Blueprint $table) {
        $table->dropColumn('emergency_contact_relationship');
    });
}
};
