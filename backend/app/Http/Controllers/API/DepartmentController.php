<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query();

        if ($request->has('level')) {
            $query->where('level', $request->query('level'));
        }

        return response()->json($query->orderBy('department_name')->get());
    }
}