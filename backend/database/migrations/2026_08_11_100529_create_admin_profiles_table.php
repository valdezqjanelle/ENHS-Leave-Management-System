<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      Schema::create('admin_profiles', function (Blueprint $table) {
    $table->id('admin_profile_id');

    $table->unsignedBigInteger('user_id');

    $table->foreign('user_id')
        ->references('user_id')
        ->on('users')
        ->onDelete('cascade');

    $table->string('first_name');
    $table->string('middle_name')->nullable();
    $table->string('last_name');
    $table->string('sex')->nullable();
    $table->string('position')->nullable();
    $table->string('department')->nullable();
    $table->string('contact_number')->nullable();

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_profiles');
    }
};