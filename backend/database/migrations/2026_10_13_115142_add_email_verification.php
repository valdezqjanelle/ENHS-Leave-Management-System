<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable();
        });
        Schema::create('email_verification_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->string('email');
            $table->string('password_hash_at_issue');
            $table->string('challenge_hash', 64)->unique();
            $table->timestamp('challenge_expires_at');
            $table->string('otp_hash')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamp('window_started_at');
            $table->unsignedInteger('send_count')->default(0);
            $table->unsignedInteger('attempts')->default(0);
        });
        // NULL for every existing account; preserve all passwords and records.
    }
    public function down(): void
    {
        Schema::dropIfExists('email_verification_codes');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('email_verified_at'));
    }
};
