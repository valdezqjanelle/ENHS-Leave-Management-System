<?php

namespace App\Support;

use App\Models\SystemSetting;

/**
 * Names of the two CS Form No. 6 approving authorities that have no signature:
 *   - 7.A  Authorized Officer
 *   - School Division Superintendent
 *
 * They are stored as plain key/value rows in the existing `system_settings`
 * table, independent of the School Principal's `authorized_signatories` record.
 */
class ApprovingAuthorities
{
    public const OFFICER_KEY = 'authorized_officer_name';

    public const SUPERINTENDENT_KEY = 'school_division_superintendent_name';

    public static function officerName(): string
    {
        return self::get(self::OFFICER_KEY);
    }

    public static function superintendentName(): string
    {
        return self::get(self::SUPERINTENDENT_KEY);
    }

    public static function get(string $key): string
    {
        return trim((string) SystemSetting::query()
            ->where('setting_key', $key)
            ->value('setting_value'));
    }

    public static function set(string $key, string $value): void
    {
        SystemSetting::query()->updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
    }
}
