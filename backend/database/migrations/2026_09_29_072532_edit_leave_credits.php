<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_credits', function (Blueprint $table) {
            $table->string('posted_bucket')->nullable();
            $table->decimal('posted_days', 8, 2)->nullable();
            $table->string('revoked_reason', 500)->nullable();
            $table->unsignedBigInteger('revoked_by')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->foreign('revoked_by')->references('user_id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::table('leave_credits', function (Blueprint $table) {
            $table->dropForeign(['revoked_by']);
            $table->dropColumn(['posted_bucket', 'posted_days', 'revoked_reason', 'revoked_by', 'revoked_at']);
        });
    }
};