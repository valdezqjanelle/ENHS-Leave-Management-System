<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authorized_signatories', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name');
            $table->string('designation');
            $table->string('signature_path')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('leave_applications', function (Blueprint $table) {
            $table->string('signatory_name_snapshot')->nullable();
            $table->string('signatory_designation_snapshot')->nullable();
            $table->string('signatory_signature_path_snapshot')->nullable();
            $table->boolean('signatory_snapshot_locked')->default(false);
        });

        DB::table('leave_applications')
            ->where('final_status', '!=', 'pending')
            ->update(['signatory_snapshot_locked' => true]);
    }

    public function down(): void
    {
        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn([
                'signatory_name_snapshot',
                'signatory_designation_snapshot',
                'signatory_signature_path_snapshot',
                'signatory_snapshot_locked',
            ]);
        });

        Schema::dropIfExists('authorized_signatories');
    }
};
