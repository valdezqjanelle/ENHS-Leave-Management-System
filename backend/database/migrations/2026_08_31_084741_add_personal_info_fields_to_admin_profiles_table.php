<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->string('extension_name', 50)->nullable()->after('last_name');
            $table->date('date_of_birth')->nullable()->after('extension_name');
            $table->string('civil_status', 50)->nullable()->after('sex');
            $table->string('nationality', 100)->nullable()->after('civil_status');
            $table->text('address')->nullable()->after('nationality');
            $table->string('personal_email')->nullable()->after('address');
            $table->string('emergency_contact_name')->nullable()->after('contact_number');
            $table->string('emergency_contact_number', 50)->nullable()->after('emergency_contact_name');
        });
    }

    public function down(): void
    {
        Schema::table('admin_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'extension_name',
                'date_of_birth',
                'civil_status',
                'nationality',
                'address',
                'personal_email',
                'emergency_contact_name',
                'emergency_contact_number',
            ]);
        });
    }
};