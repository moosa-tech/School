<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $schoolId = session('current_school_id');
        $years = AcademicYear::forSchool($schoolId)->orderBy('starts_on', 'desc')->get();

        return view('school-admin.academic-years.index', compact('years'));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
            'is_current' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($schoolId, $validated, $request) {
            $isCurrent = $request->boolean('is_current');

            if ($isCurrent) {
                AcademicYear::forSchool($schoolId)->update(['is_current' => false]);
            }

            AcademicYear::create([
                'school_id' => $schoolId,
                'name' => $validated['name'],
                'starts_on' => $validated['starts_on'],
                'ends_on' => $validated['ends_on'],
                'is_current' => $isCurrent,
                'status' => 'active',
            ]);
        });

        return back()->with('status', 'Academic Year created successfully.');
    }

    public function setCurrent(AcademicYear $academicYear): RedirectResponse
    {
        $schoolId = session('current_school_id');

        // Tenant safety check
        if ($academicYear->school_id !== $schoolId) {
            abort(403);
        }

        DB::transaction(function () use ($schoolId, $academicYear) {
            AcademicYear::forSchool($schoolId)->update(['is_current' => false]);
            $academicYear->update(['is_current' => true]);
        });

        return back()->with('status', "Academic Year '{$academicYear->name}' is now set as the active current year.");
    }
}
