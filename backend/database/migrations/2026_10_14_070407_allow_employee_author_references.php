<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Nullable author references preserve other people's agency records
        // while permitting complete removal of the deleted login account.
        foreach (
            [
                ['employee_records', 'created_by', 'created_by_name'],
                ['leave_credits', 'recorded_by', 'recorded_by_name'],
                ['leave_credits', 'revoked_by', 'revoked_by_name'],
                ['leave_balance_adjustments', 'admin_user_id', 'admin_user_name'],
                ['reports', 'generated_by', 'generated_by_name'],
                ['backups', 'created_by', 'created_by_name'],
                ['leave_school_years', 'processed_by', 'processed_by_name'],
            ] as [$tableName, $column, $snapshot]
        ) {
            if (!Schema::hasColumn($tableName, $column)) continue;
            Schema::table($tableName, function (Blueprint $table) use ($column) {
                $table->unsignedBigInteger($column)->nullable()->change();
            });
            if (!Schema::hasColumn($tableName, $snapshot)) {
                Schema::table($tableName, function (Blueprint $table) use ($snapshot) {
                    $table->string($snapshot, 255)->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        // Keep attribution snapshots and nullable columns. Restoring NOT NULL
        // after accounts have been removed would fail or require invented authors.
    }
};
