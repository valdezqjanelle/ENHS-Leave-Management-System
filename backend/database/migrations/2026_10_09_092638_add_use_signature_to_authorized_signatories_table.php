<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authorized_signatories', function (Blueprint $table) {
            // true = print the stored e-signature on CS Form No. 6,
            // false = leave the space blank so the signatory signs by hand (wet signature).
            $table->boolean('use_signature')->default(true)->after('signature_path');
        });
    }

    public function down(): void
    {
        Schema::table('authorized_signatories', function (Blueprint $table) {
            $table->dropColumn('use_signature');
        });
    }
};