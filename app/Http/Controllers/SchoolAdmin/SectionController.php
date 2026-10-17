<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Support\SchoolAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'class_id' => ['required', SchoolAccess::existsInSchool('classes', $schoolId)],
            'name' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);

        $class = SchoolClass::forSchool($schoolId)->findOrFail($validated['class_id']);

        Section::create([
            'school_id' => $schoolId,
            'class_id' => $class->id,
            'name' => $validated['name'],
            'capacity' => $validated['capacity'],
            'status' => 'active',
        ]);

        return back()->with('status', "Section '{$validated['name']}' added to {$class->name}.");
    }
}
