<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_settings', function (Blueprint $table) {
            $table->id('approval_setting_id');
            $table->boolean('require_admin_review')->default(true);
            $table->boolean('require_principal_endorsement')->default(true);
            $table->boolean('allow_admin_remarks')->default(true);
            $table->boolean('auto_update_status')->default(true);
            $table->boolean('auto_update_balance')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_settings');
    }
};