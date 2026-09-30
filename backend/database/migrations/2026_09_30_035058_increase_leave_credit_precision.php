<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('leave_credits', function (Blueprint $table) {
            $table->decimal('hours_rendered', 12, 4)->change();
            $table->decimal('equivalent_leave_days', 12, 3)->change();
            if (Schema::hasColumn('leave_credits', 'posted_days')) {
                $table->decimal('posted_days', 12, 3)->nullable()->change();
            }
        });
        Schema::table('leave_balances', function (Blueprint $table) {
            foreach (['vacation_earned', 'sick_earned', 'vacation_balance',
                      'sick_balance', 'service_credits', 'used_leave'] as $column) {
                if (Schema::hasColumn('leave_balances', $column)) {
                    $table->decimal($column, 12, 3)->default(0)->change();
                }
            }
        });
    }
    public function down(): void
    {
        // Intentionally retain precision; reverting to two decimals loses data.
    }
};
