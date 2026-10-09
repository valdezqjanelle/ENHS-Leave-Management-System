<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            // When an admin moved the application out of "pending"
            // (approved or disapproved). Used for "Approved This Month".
            $table->timestamp('reviewed_at')->nullable()->after('final_status');
            $table->index(['final_status', 'reviewed_at'], 'leave_apps_status_reviewed_idx');
            $table->index(['final_status', 'start_date', 'end_date'], 'leave_apps_status_dates_idx');
        });

        // Best available approximation for applications that were reviewed
        // before this column existed.
        DB::table('leave_applications')
            ->whereIn('final_status', ['approved', 'disapproved'])
            ->whereNull('reviewed_at')
            ->update(['reviewed_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropIndex('leave_apps_status_reviewed_idx');
            $table->dropIndex('leave_apps_status_dates_idx');
            $table->dropColumn('reviewed_at');
        });
    }
};
