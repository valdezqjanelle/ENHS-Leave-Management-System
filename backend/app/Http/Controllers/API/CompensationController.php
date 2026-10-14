<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CompensationController extends Controller
{
    private function lock(): void
    {
        DB::table('salary_management_control')->where('id', 1)->lockForUpdate()->firstOrFail();
    }
    private function positionUsage(int $id): int
    {
        return DB::table('employee_records')->where('position_id', $id)->count()
            + DB::table('admin_profiles')->where('position_id', $id)->count();
    }
    public function positions()
    {
        return response()->json(Position::orderBy('name')->get()->map(function ($position) {
            $position->setAttribute('assigned_records', $this->positionUsage($position->id));
            return $position;
        }));
    }
    private function positionData(Request $request, ?int $id = null): array
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code'))), 'name' => trim((string) $request->input('name'))]);
        return $request->validate([
            'code' => ['required', 'string', 'max:30', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('positions', 'code')->ignore($id)],
            'name' => 'required|string|max:255',
            'type' => 'required|in:Teaching,Non-Teaching,School Head',
            'salary_grade' => 'nullable|integer|min:1|max:33',
            'is_active' => 'required|boolean',
        ]);
    }
    public function storePosition(Request $request)
    {
        $data = $this->positionData($request);
        $position = DB::transaction(function () use ($data) {
            $this->lock();
            $position = Position::create($data);
            AuditLogger::log('Position added', "Added position #{$position->id} {$position->name}");
            return $position;
        });
        return response()->json(['message' => 'Position added.', 'data' => $position], 201);
    }
    public function updatePosition(Request $request, int $id)
    {
        // Table status actions change only is_active, not stale copies of other fields.
        $statusOnly = array_keys($request->all()) === ['is_active'];
        $data = $statusOnly ? $request->validate(['is_active' => 'required|boolean']) : $this->positionData($request, $id);
        $position = DB::transaction(function () use ($data, $id, $statusOnly) {
            $this->lock();
            $position = Position::lockForUpdate()->findOrFail($id);
            if (!$statusOnly && $this->positionUsage($id) > 0 && ($position->type !== $data['type']
                || (string) $position->salary_grade !== (string) ($data['salary_grade'] ?? ''))) {
                throw ValidationException::withMessages(['salary_grade' => 'This position is already assigned. Create a new position to change its personnel type or salary grade, then update the affected employee records.']);
            }
            $position->update($data);
            AuditLogger::log('Position updated', "Updated position #{$id} {$position->name}; active: " . ($position->is_active ? 'yes' : 'no'));
            return $position;
        });
        return response()->json(['message' => 'Position updated.', 'data' => $position]);
    }
    public function schedules()
    {
        return response()->json(DB::table('salary_schedule_versions')->orderByDesc('id')->get()->map(function ($schedule) {
            $schedule->rate_count = DB::table('salary_schedule_rates')->where('schedule_id', $schedule->id)->count();
            return $schedule;
        }));
    }
    private function details(int $id): array
    {
        return [
            'schedule' => DB::table('salary_schedule_versions')->where('id', $id)->firstOrFail(),
            'rates' => DB::table('salary_schedule_rates')->where('schedule_id', $id)->orderBy('salary_grade')->orderBy('step')->get()
        ];
    }
    public function showSchedule(int $id)
    {
        return response()->json($this->details($id));
    }
    public function storeSchedule(Request $request)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:salary_schedule_versions,name',
            'effective_date' => 'required|date_format:Y-m-d',
            'source_reference' => 'nullable|string|max:500',
            'copy_from_id' => 'nullable|integer|exists:salary_schedule_versions,id'
        ]);
        $id = DB::transaction(function () use ($data, $request) {
            $this->lock();
            $id = DB::table('salary_schedule_versions')->insertGetId([
                'name' => trim($data['name']),
                'effective_date' => $data['effective_date'],
                'source_reference' => $data['source_reference'] ?? null,
                'created_by' => $request->user()->user_id,
                'status' => 'draft',
                'revision' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $copyId = $data['copy_from_id'] ?? DB::table('salary_schedule_versions')->where('status', 'active')->value('id');
            if ($copyId) {
                foreach (DB::table('salary_schedule_rates')->where('schedule_id', $copyId)->get()->chunk(250) as $chunk) {
                    DB::table('salary_schedule_rates')->insert($chunk->map(fn($rate) => [
                        'schedule_id' => $id,
                        'salary_grade' => $rate->salary_grade,
                        'step' => $rate->step,
                        'salary' => $rate->salary,
                    ])->all());
                }
            }
            AuditLogger::log('Salary schedule created', "Created draft salary schedule #{$id} {$data['name']}");
            return $id;
        });
        return response()->json($this->details($id), 201);
    }
    public function updateSchedule(Request $request, int $id)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('salary_schedule_versions', 'name')->ignore($id)],
            'effective_date' => 'required|date_format:Y-m-d',
            'source_reference' => 'nullable|string|max:500',
            'revision' => 'required|integer|min:1',
            'rates' => 'present|array|max:264',
            'rates.*.salary_grade' => 'required|integer|min:1|max:33',
            'rates.*.step' => 'required|integer|min:1|max:8',
            'rates.*.salary' => ['present', 'nullable', 'numeric', 'min:0.01', 'max:99999999.99', 'regex:/^\d{1,8}(\.\d{1,2})?$/'],
        ]);
        $keys = array_map(fn($rate) => $rate['salary_grade'] . '-' . $rate['step'], $data['rates']);
        if (count($keys) !== count(array_unique($keys))) throw ValidationException::withMessages(['rates' => 'Each salary grade and step may appear only once.']);
        DB::transaction(function () use ($data, $id) {
            $this->lock();
            $schedule = $this->draft($id, $data['revision']);
            DB::table('salary_schedule_versions')->where('id', $id)->update([
                'name' => trim($data['name']),
                'effective_date' => $data['effective_date'],
                'source_reference' => $data['source_reference'] ?? null,
                'revision' => $schedule->revision + 1,
                'updated_at' => now(),
            ]);
            DB::table('salary_schedule_rates')->where('schedule_id', $id)->delete();
            foreach (array_chunk($data['rates'], 250) as $chunk) {
                DB::table('salary_schedule_rates')->insert(array_map(fn($rate) => [
                    'schedule_id' => $id,
                    'salary_grade' => $rate['salary_grade'],
                    'step' => $rate['step'],
                    'salary' => $rate['salary'] === null ? null : number_format((float) $rate['salary'], 2, '.', ''),
                ], $chunk));
            }
            AuditLogger::log('Salary schedule updated', "Updated draft salary schedule #{$id}");
        });
        return response()->json($this->details($id));
    }
    private function draft(int $id, int $revision)
    {
        $schedule = DB::table('salary_schedule_versions')->where('id', $id)->lockForUpdate()->firstOrFail();
        abort_unless($schedule->status === 'draft', 409, 'Active and archived schedules are read-only. Create a new draft to make changes.');
        abort_unless((int) $schedule->revision === $revision, 409, 'This draft changed in another session. Reload it before saving or activating.');
        return $schedule;
    }
    private function preview($schedule): array
    {
        $rates = DB::table('salary_schedule_rates')->where('schedule_id', $schedule->id)->orderBy('salary_grade')->orderBy('step')->get();
        $errors = [];
        if ($rates->isEmpty()) $errors[] = 'Add salary rates before activation.';
        if ($rates->contains(fn($rate) => $rate->salary === null || (float) $rate->salary <= 0 || (float) $rate->salary > 99999999.99)) $errors[] = 'Complete every salary step with a positive amount before activation. Incomplete drafts may be saved, but cannot be activated.';
        foreach ($rates->groupBy('salary_grade') as $grade => $rows) {
            if ($rows->pluck('step')->map(fn($step) => (int) $step)->sort()->values()->all() !== range(1, 8)) $errors[] = "Salary Grade {$grade} needs all eight steps.";
        }
        $available = $rates->pluck('salary_grade')->map(fn($grade) => (int) $grade)->unique();
        $needed = Position::where(function ($query) {
            $query->where('is_active', true)
                ->orWhereIn('id', DB::table('employee_records')->select('position_id')->whereNotNull('position_id'))
                ->orWhereIn('id', DB::table('admin_profiles')->select('position_id')->whereNotNull('position_id'));
        })->whereNotNull('salary_grade')->pluck('salary_grade')->map(fn($grade) => (int) $grade)->unique();
        $missing = $needed->diff($available)->values();
        if ($missing->isNotEmpty()) $errors[] = 'Missing rates for active or assigned position salary grades: ' . $missing->implode(', ');
        $active = DB::table('salary_schedule_versions')->where('status', 'active')->first();
        if (!$schedule->effective_date || $schedule->effective_date > today()->toDateString()) $errors[] = 'Activate on or after the effective date.';
        if ($active?->effective_date && $schedule->effective_date < $active->effective_date) $errors[] = 'The effective date cannot be earlier than the current active schedule.';
        $current = DB::table('salary_schedules')->get()->keyBy(fn($rate) => $rate->salary_grade . '-' . $rate->step);
        $changes = [];
        foreach ($rates as $rate) {
            $old = $current->get($rate->salary_grade . '-' . $rate->step)?->salary;
            if ($old === null || number_format((float) $old, 2, '.', '') !== number_format((float) $rate->salary, 2, '.', '')) {
                $changes[] = ['salary_grade' => $rate->salary_grade, 'step' => $rate->step, 'before' => $old, 'after' => $rate->salary];
            }
        }
        $newKeys = $rates->map(fn($rate) => $rate->salary_grade . '-' . $rate->step)->all();
        foreach ($current as $key => $old) {
            if (!in_array($key, $newKeys, true)) $changes[] = ['salary_grade' => $old->salary_grade, 'step' => $old->step, 'before' => $old->salary, 'after' => null];
        }
        return [
            'can_activate' => count($errors) === 0,
            'errors' => $errors,
            'changes' => $changes,
            'rate_count' => $rates->count(),
            'active_schedule_id' => $active?->id,
            'existing_employee_salaries_updated' => false
        ];
    }
    public function previewSchedule(Request $request, int $id)
    {
        $data = $request->validate(['revision' => 'required|integer|min:1']);
        return DB::transaction(function () use ($id, $data) {
            $this->lock();
            return response()->json($this->preview($this->draft($id, $data['revision'])));
        });
    }
    public function activateSchedule(Request $request, int $id)
    {
        $data = $request->validate(['revision' => 'required|integer|min:1', 'expected_active_id' => 'nullable|integer', 'confirm' => 'required|accepted']);
        DB::transaction(function () use ($request, $data, $id) {
            $this->lock();
            $schedule = $this->draft($id, $data['revision']);
            $preview = $this->preview($schedule);
            abort_unless((int) ($preview['active_schedule_id'] ?? 0) === (int) ($data['expected_active_id'] ?? 0), 409, 'The active salary schedule changed. Preview again before activating.');
            if (!$preview['can_activate']) throw ValidationException::withMessages(['schedule' => $preview['errors']]);
            DB::table('salary_schedule_versions')->where('status', 'active')->update(['status' => 'archived', 'updated_at' => now()]);
            DB::table('salary_schedule_versions')->where('id', $id)->update([
                'status' => 'active',
                'activated_by' => $request->user()->user_id,
                'activated_at' => now(),
                'revision' => $schedule->revision + 1,
                'updated_at' => now(),
            ]);
            // Keep all existing grade/step lookup APIs compatible, atomically.
            DB::table('salary_schedules')->delete();
            foreach (DB::table('salary_schedule_rates')->where('schedule_id', $id)->get()->chunk(250) as $chunk) {
                DB::table('salary_schedules')->insert($chunk->map(fn($rate) => [
                    'salary_grade' => $rate->salary_grade,
                    'step' => $rate->step,
                    'salary' => $rate->salary,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all());
            }
            AuditLogger::log('Salary schedule activated', "Activated salary schedule #{$id} {$schedule->name}; existing employee salaries unchanged.");
        });
        return response()->json(['message' => 'Salary schedule activated. Existing employee salary records were not rewritten.']);
    }
    public function destroySchedule(Request $request, int $id)
    {
        $data = $request->validate(['revision' => 'required|integer|min:1']);
        DB::transaction(function () use ($data, $id) {
            $this->lock();
            $this->draft($id, $data['revision']);
            DB::table('salary_schedule_versions')->where('id', $id)->delete();
            AuditLogger::log('Salary draft deleted', "Deleted salary schedule draft #{$id}");
        });
        return response()->json(['message' => 'Draft deleted.']);
    }
}
