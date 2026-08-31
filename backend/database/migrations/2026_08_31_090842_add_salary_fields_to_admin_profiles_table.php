<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('salary_step')->nullable()->after('position_id');
            $table->decimal('salary', 10, 2)->nullable()->after('salary_step');
        });
    }

    public function down(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->dropColumn(['salary_step', 'salary']);
        });
    }
};