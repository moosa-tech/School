<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function index(): View
    {
        $schoolId = session('current_school_id');
        $classes = SchoolClass::forSchool($schoolId)->with(['sections'])->orderBy('sort_order')->get();

        return view('school-admin.classes.index', compact('classes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $class = SchoolClass::create([
            'school_id' => $schoolId,
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'status' => 'active',
        ]);

        // Default section 'A'
        Section::create([
            'school_id' => $schoolId,
            'class_id' => $class->id,
            'name' => 'Section A',
            'capacity' => 40,
            'status' => 'active',
        ]);

        return back()->with('status', "Class '{$class->name}' added with default Section A.");
    }
}
