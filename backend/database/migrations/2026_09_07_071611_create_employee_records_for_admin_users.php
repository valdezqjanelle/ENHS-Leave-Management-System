<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $admins = DB::table('users')
            ->leftJoin('admin_profiles', 'users.user_id', '=', 'admin_profiles.user_id')
            ->where('users.role', 'admin')
            ->select([
                'users.user_id as account_user_id',
                'users.email as account_email',
                'admin_profiles.first_name',
                'admin_profiles.middle_name',
                'admin_profiles.last_name',
                'admin_profiles.extension_name',
                'admin_profiles.date_of_birth',
                'admin_profiles.sex',
                'admin_profiles.civil_status',
                'admin_profiles.nationality',
                'admin_profiles.address',
                'admin_profiles.contact_number',
                'admin_profiles.personal_email',
                'admin_profiles.emergency_contact_name',
                'admin_profiles.emergency_contact_number',
                'admin_profiles.level',
                'admin_profiles.department_id',
                'admin_profiles.position_id',
                'admin_profiles.salary_step',
                'admin_profiles.salary',
                'admin_profiles.created_at as profile_created_at',
            ])
            ->get();

        foreach ($admins as $admin) {
            $exists = DB::table('employee_records')
                ->where('user_id', $admin->account_user_id)
                ->exists();

            if ($exists) {
                continue;
            }

            $employeeCode = 'ADM-' . str_pad((string) $admin->account_user_id, 4, '0', STR_PAD_LEFT);

            DB::table('employee_records')->insert([
                'user_id' => $admin->account_user_id,
                'created_by' => $admin->account_user_id,
                'employee_code' => $employeeCode,
                'first_name' => $admin->first_name ?? 'Admin',
                'middle_name' => $admin->middle_name ?? null,
                'last_name' => $admin->last_name ?? 'Personnel',
                'extension_name' => $admin->extension_name ?? null,
                'date_of_birth' => $admin->date_of_birth ?? null,
                'sex' => $admin->sex ?? 'Not specified',
                'civil_status' => $admin->civil_status ?? null,
                'nationality' => $admin->nationality ?? null,
                'address' => $admin->address ?? null,
                'contact_number' => $admin->contact_number ?? null,
                'personal_email' => $admin->personal_email ?? $admin->account_email,
                'emergency_contact_name' => $admin->emergency_contact_name ?? null,
                'emergency_contact_number' => $admin->emergency_contact_number ?? null,
                'personnel_type' => 'Non-Teaching',
                'employment_status' => 'active',
                'employment_category' => null,
                'date_hired' => isset($admin->profile_created_at)
                    ? date('Y-m-d', strtotime($admin->profile_created_at))
                    : now()->toDateString(),
                'level' => $admin->level ?? 'Non-Teaching',
                'department_id' => $admin->department_id ?? null,
                'position_id' => $admin->position_id ?? null,
                'supervisor_id' => null,
                'salary_step' => $admin->salary_step ?? 1,
                'salary' => $admin->salary ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('employee_records')
            ->whereIn('user_id', function ($query) {
                $query->select('user_id')
                    ->from('users')
                    ->where('role', 'admin');
            })
            ->where('employee_code', 'like', 'ADM-%')
            ->delete();
    }
};
