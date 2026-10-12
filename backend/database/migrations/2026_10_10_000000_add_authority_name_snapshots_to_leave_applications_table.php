<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            // Captured when an application leaves "pending", alongside the
            // authorized signatory snapshot, so finalized forms keep the names
            // they were approved with. Null = finalized before these settings
            // existed (or no name was set), and the form prints the line blank.
            $table->string('authorized_officer_name_snapshot')->nullable();
            $table->string('school_division_superintendent_name_snapshot')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn([
                'authorized_officer_name_snapshot',
                'school_division_superintendent_name_snapshot',
            ]);
        });
    }
};
