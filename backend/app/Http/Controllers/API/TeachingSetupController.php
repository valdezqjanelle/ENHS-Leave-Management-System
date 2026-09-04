<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\GradeLevel;
use App\Models\SchoolSection;
use App\Models\TeachingSubject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeachingSetupController extends Controller
{
    public function index()
    {
        return response()->json([
            'grade_levels' => GradeLevel::with(['sections' => fn ($q) => $q->orderBy('section_name')])->orderBy('sort_order')->get(),
            'subjects' => TeachingSubject::with('department')->orderBy('subject_name')->get(),
        ]);
    }

    public function storeGradeLevel(Request $request)
    {
        $data = $request->validate([
            'grade_name' => ['required', 'string', 'max:100', Rule::unique('grade_levels')->where(fn ($q) => $q->where('level', $request->level))],
            'level' => 'required|in:JHS,SHS', 'sort_order' => 'nullable|integer|min:0', 'is_active' => 'nullable|boolean',
        ]);
        return response()->json(GradeLevel::create($data), 201);
    }

    public function updateGradeLevel(Request $request, $id)
    {
        $item = GradeLevel::findOrFail($id);
        $data = $request->validate([
            'grade_name' => ['required', 'string', 'max:100', Rule::unique('grade_levels')->ignore($id, 'grade_level_id')->where(fn ($q) => $q->where('level', $request->level))],
            'level' => 'required|in:JHS,SHS', 'sort_order' => 'nullable|integer|min:0', 'is_active' => 'required|boolean',
        ]);
        $item->update($data);
        return response()->json($item);
    }

    public function destroyGradeLevel($id)
    {
        $item = GradeLevel::findOrFail($id);
        if ($item->assignments()->exists()) {
            return response()->json(['message' => 'This grade level is already used. Mark it inactive instead of removing it.'], 422);
        }
        $item->delete();
        return response()->json(['message' => 'Grade level removed.']);
    }

    public function storeSection(Request $request)
    {
        $data = $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,grade_level_id',
            'section_name' => ['required', 'string', 'max:100', Rule::unique('school_sections')->where(fn ($q) => $q->where('grade_level_id', $request->grade_level_id))],
            'is_active' => 'nullable|boolean',
        ]);
        return response()->json(SchoolSection::create($data), 201);
    }

    public function updateSection(Request $request, $id)
    {
        $item = SchoolSection::findOrFail($id);
        $data = $request->validate([
            'grade_level_id' => 'required|exists:grade_levels,grade_level_id',
            'section_name' => ['required', 'string', 'max:100', Rule::unique('school_sections')->ignore($id, 'section_id')->where(fn ($q) => $q->where('grade_level_id', $request->grade_level_id))],
            'is_active' => 'required|boolean',
        ]);
        $item->update($data);
        return response()->json($item);
    }

    public function destroySection($id)
    {
        $item = SchoolSection::findOrFail($id);
        if ($item->assignments()->exists()) {
            return response()->json(['message' => 'This section is already used. Mark it inactive instead of removing it.'], 422);
        }
        $item->delete();
        return response()->json(['message' => 'Section removed.']);
    }

    public function storeSubject(Request $request)
    {
        $data = $request->validate([
            'department_id' => 'nullable|exists:departments,department_id',
            'subject_name' => ['required', 'string', 'max:150', Rule::unique('teaching_subjects')->where(fn ($q) => $q->where('department_id', $request->department_id)->where('level', $request->level))],
            'level' => 'required|in:JHS,SHS', 'is_active' => 'nullable|boolean',
        ]);
        return response()->json(TeachingSubject::create($data), 201);
    }

    public function updateSubject(Request $request, $id)
    {
        $item = TeachingSubject::findOrFail($id);
        $data = $request->validate([
            'department_id' => 'nullable|exists:departments,department_id',
            'subject_name' => ['required', 'string', 'max:150', Rule::unique('teaching_subjects')->ignore($id, 'subject_id')->where(fn ($q) => $q->where('department_id', $request->department_id)->where('level', $request->level))],
            'level' => 'required|in:JHS,SHS', 'is_active' => 'required|boolean',
        ]);
        $item->update($data);
        return response()->json($item->load('department'));
    }

    public function destroySubject($id)
    {
        $item = TeachingSubject::findOrFail($id);
        if ($item->assignments()->exists()) {
            return response()->json(['message' => 'This subject is already used. Mark it inactive instead of removing it.'], 422);
        }
        $item->delete();
        return response()->json(['message' => 'Subject removed.']);
    }
}
