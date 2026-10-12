<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('password_recovery_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('otp_hash')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('last_sent_at');
            $table->timestamp('window_started_at');
            $table->unsignedInteger('send_count')->default(0);
            $table->unsignedInteger('attempts')->default(0);
            $table->string('reset_hash', 64)->nullable();
            $table->timestamp('reset_expires_at')->nullable();
            $table->string('password_hash_at_issue');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('password_recovery_codes');
    }
};
