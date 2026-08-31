<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_profiles', 'position_id')) {
                $table->unsignedBigInteger('position_id')->nullable();
            }

            if (!Schema::hasColumn('admin_profiles', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('admin_profiles', 'position_id')) {
                $table->dropColumn('position_id');
            }

            if (Schema::hasColumn('admin_profiles', 'department_id')) {
                $table->dropColumn('department_id');
            }
        });
    }
};