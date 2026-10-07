<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Renumbers leave_types.leave_type_id so the standard leave types follow the order of
 * the CS Form 6 checkboxes (1-13), "Other purposes" is 14, and custom leave types
 * added by the admin (e.g. Local Leave) continue from 15, keeping their relative order.
 *
 * leave_applications.leave_type_id is updated together with it, so existing
 * applications keep pointing to the same leave type.
 */
return new class extends Migration
{
    /** Standard types in CS Form 6 order: slot => [code, name prefix]. */
    private const SLOTS = [
        1  => ['VL',   'vacation leave'],
        2  => ['FL',   'mandatory'],
        3  => ['SL',   'sick leave'],
        4  => ['ML',   'maternity leave'],
        5  => ['PL',   'paternity leave'],
        6  => ['SPL',  'special privilege leave'],
        7  => ['SOLO', 'solo parent leave'],
        8  => ['STL',  'study leave'],
        9  => ['VAWC', '10-day vawc'],
        10 => ['RP',   'rehabilitation'],
        11 => ['SLBW', 'special leave benefits for women'],
        12 => ['SEL',  'special emergency'],
        13 => ['AL',   'adoption leave'],
        14 => ['OP',   'other purposes'],
    ];

    private const TEMP_OFFSET = 1000000;

    public function up(): void
    {
        $rows = DB::table('leave_types')->orderBy('leave_type_id')->get();

        // old id => new id
        $map = [];
        $taken = [];

        foreach ($rows as $row) {
            $slot = $this->slotFor($row);

            if ($slot !== null && !isset($taken[$slot])) {
                $map[$row->leave_type_id] = $slot;
                $taken[$slot] = true;
            }
        }

        // Everything that is not a standard type keeps its relative order, from 15 up.
        $next = 15;
        foreach ($rows as $row) {
            if (!isset($map[$row->leave_type_id])) {
                $map[$row->leave_type_id] = $next++;
            }
        }

        // Nothing to do if the ids are already in the right place.
        if (collect($map)->every(fn ($new, $old) => (int) $old === (int) $new)) {
            return;
        }

        Schema::table('leave_applications', function (Blueprint $table) {
            $table->dropForeign(['leave_type_id']);
        });

        // Phase 1: move every id out of the way so the final ids cannot collide.
        DB::table('leave_types')->increment('leave_type_id', self::TEMP_OFFSET);
        DB::table('leave_applications')->increment('leave_type_id', self::TEMP_OFFSET);

        // Phase 2: set the final ids.
        foreach ($map as $old => $new) {
            DB::table('leave_types')
                ->where('leave_type_id', $old + self::TEMP_OFFSET)
                ->update(['leave_type_id' => $new]);

            DB::table('leave_applications')
                ->where('leave_type_id', $old + self::TEMP_OFFSET)
                ->update(['leave_type_id' => $new]);
        }

        Schema::table('leave_applications', function (Blueprint $table) {
            $table->foreign('leave_type_id')
                ->references('leave_type_id')
                ->on('leave_types');
        });

        // Make the next inserted leave type get the next free id.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                "SELECT setval(pg_get_serial_sequence('leave_types', 'leave_type_id'), "
                . "GREATEST((SELECT COALESCE(MAX(leave_type_id), 1) FROM leave_types), 14))"
            );
        }
    }

    public function down(): void
    {
        // The previous numbering was not meaningful and is not restored.
    }

    private function slotFor(object $row): ?int
    {
        $code = strtoupper(trim((string) $row->code));
        $name = strtolower(trim((string) $row->leave_type_name));

        foreach (self::SLOTS as $slot => [$slotCode, $prefix]) {
            if ($code !== '' && $code === $slotCode) {
                return $slot;
            }
        }

        foreach (self::SLOTS as $slot => [$slotCode, $prefix]) {
            if (str_starts_with($name, $prefix)) {
                return $slot;
            }
        }

        return null;
    }
};