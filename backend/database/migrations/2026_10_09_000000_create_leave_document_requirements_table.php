<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_document_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_type_id');
            $table->string('document_name');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('requirement_type', 20)->default('always');
            $table->unsignedInteger('condition_days')->nullable();
            $table->boolean('condition_filed_in_advance')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('leave_type_id')
                ->references('leave_type_id')
                ->on('leave_types')
                ->cascadeOnDelete();
            $table->index(['leave_type_id', 'is_active', 'sort_order']);
        });

        Schema::table('leave_applications', function (Blueprint $table) {
            $table->json('document_requirements_snapshot')->nullable();
        });

        Schema::table('leave_attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('leave_document_requirement_id')->nullable();
            $table->string('stored_filename')->nullable();
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamp('uploaded_at')->nullable();

            $table->foreign('leave_document_requirement_id')
                ->references('id')
                ->on('leave_document_requirements')
                ->nullOnDelete();
        });

        $examples = [
            ['SL', 'Medical Certificate', 'Required when filed in advance or when leave exceeds five calendar days, matching the current Sick Leave guidance. Administrators may edit these conditions.', true, 'conditional', 6, true],
            ['ML', 'Medical/Supporting Certificate', 'Provide the applicable maternity supporting certificate or proof.', true, 'always', null, false],
            ['PL', 'Birth Certificate', 'Provide proof of the child\'s delivery, such as a birth certificate.', true, 'always', null, false],
            ['STL', 'Study Leave Contract', 'Provide the required agreement between the employee and agency head or authorized representative.', true, 'always', null, false],
            ['STL', 'Clearance', 'Required when the study leave application is 30 calendar days or more.', true, 'conditional', 30, false],
            ['SOLO', 'Solo Parent Certification/ID', 'Provide a valid, updated Solo Parent Identification Card.', true, 'always', null, false],
            ['RP', 'Rehabilitation Supporting Documents', 'Provide the applicable medical certificate and supporting documents.', true, 'always', null, false],
            ['SEL', 'Emergency Supporting Document', 'Provide proof that the employee resides in the declared calamity area, as verified by the head of office.', true, 'always', null, false],
            ['VAWC', 'VAWC Supporting Document', 'Provide an applicable protection order, certification, or police report.', true, 'always', null, false],
            ['SLBW', 'Medical Certificate', 'Provide the attending surgeon\'s medical certificate and clinical summary.', true, 'always', null, false],
            ['AL', 'Pre-Adoptive Placement Authority', 'Provide an authenticated copy of the DSWD authority.', true, 'always', null, false],
            ['VL', 'Clearance', 'Required when the leave application is 30 calendar days or more.', true, 'conditional', 30, false],
        ];

        foreach ($examples as [$code, $name, $description, $required, $type, $days, $filedInAdvance]) {
            $leaveType = DB::table('leave_types')->where('code', $code)->first();

            if (!$leaveType) {
                continue;
            }

            DB::table('leave_document_requirements')->insert([
                'leave_type_id' => $leaveType->leave_type_id,
                'document_name' => $name,
                'description' => $description,
                'is_required' => $required,
                'requirement_type' => $type,
                'condition_days' => $days,
                'condition_filed_in_advance' => $filedInAdvance,
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('leave_attachments', function (Blueprint $table) {
            $table->dropForeign(['leave_document_requirement_id']);
            $table->dropColumn([
                'leave_document_requirement_id',
                'stored_filename',
                'file_type',
                'file_size',
                'uploaded_at',
            ]);
        });

        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropColumn('document_requirements_snapshot');
        });

        Schema::dropIfExists('leave_document_requirements');
    }
};
