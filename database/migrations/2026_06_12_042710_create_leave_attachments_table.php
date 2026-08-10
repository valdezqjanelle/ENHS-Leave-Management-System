<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_attachments', function (Blueprint $table) {
    $table->id('attachment_id');

    $table->unsignedBigInteger('leave_id');

    $table->foreign('leave_id')
        ->references('leave_id')
        ->on('leave_applications')
        ->onDelete('cascade');

    $table->string('file_name');
    $table->string('file_path');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_attachments');
    }
};
