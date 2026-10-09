<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Services\WorkingDayCalculator;
use App\Support\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'year' => 'nullable|integer|min:1900|max:2100',
            'type' => 'nullable|string|max:100',
            'search' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $query = Holiday::query()->orderBy('date', 'asc')->orderBy('name', 'asc');

        if (isset($validated['year'])) {
            $query->whereYear('date', $validated['year']);
        }

        if (!empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (!empty($validated['search'])) {
            $search = trim($validated['search']);
            $query->where('name', 'like', "%{$search}%");
        }

        if (array_key_exists('is_active', $validated)) {
            $query->where('is_active', (bool) $validated['is_active']);
        }

        return response()->json($query->get());
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $summary = app(WorkingDayCalculator::class)->calculate(
            $validated['start_date'],
            $validated['end_date']
        );

        return response()->json($summary);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date', Rule::unique('holidays', 'date')->where(fn ($query) => $query->where('name', $request->input('name')))],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:Regular Holiday,Special Non-Working Day,School Non-Working Day,Other Non-Working Day'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $holiday = Holiday::create($validated);

        AuditLogger::log(
            'Holiday created',
            "Created holiday \"{$holiday->name}\" for {$holiday->date}."
        );

        return response()->json([
            'message' => 'Holiday created successfully.',
            'data' => $holiday,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);
        $validated = $request->validate([
            'date' => ['sometimes', 'required', 'date', Rule::unique('holidays', 'date')->ignore($holiday->id)->where(fn ($query) => $query->where('name', $request->input('name', $holiday->name)))],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', 'string', 'in:Regular Holiday,Special Non-Working Day,School Non-Working Day,Other Non-Working Day'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'required', 'boolean'],
        ]);

        $holiday->update($validated);

        AuditLogger::log(
            'Holiday updated',
            "Updated holiday \"{$holiday->fresh()->name}\" for {$holiday->fresh()->date}."
        );

        return response()->json([
            'message' => 'Holiday updated successfully.',
            'data' => $holiday->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        $holiday->delete();

        AuditLogger::log(
            'Holiday deleted',
            "Deleted holiday \"{$holiday->name}\" for {$holiday->date}."
        );

        return response()->json([
            'message' => 'Holiday deleted successfully.',
        ]);
    }
}
