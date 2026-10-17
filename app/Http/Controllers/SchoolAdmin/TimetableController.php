<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\SchoolClass;
use App\Models\TimetableEntry;
use App\Support\SchoolAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public function index(Request $request): View
    {
        $schoolId = session('current_school_id');

        $classes = SchoolClass::forSchool($schoolId)->with('sections')->get();
        $academicYears = AcademicYear::forSchool($schoolId)->get();
        $currentYear = AcademicYear::forSchool($schoolId)->where('is_current', true)->first();

        $selectedYearId = $request->academic_year_id ?? $currentYear?->id;
        $selectedSectionId = $request->section_id ?? $classes->first()?->sections->first()?->id;

        $entries = collect();
        if ($selectedYearId && $selectedSectionId) {
            $entries = TimetableEntry::forSchool($schoolId)
                ->where('academic_year_id', $selectedYearId)
                ->where('section_id', $selectedSectionId)
                ->with(['classSubject.subject', 'classSubject.teacher'])
                ->orderBy('starts_at')
                ->get()
                ->groupBy('weekday');
        }

        $classSubjects = ClassSubject::forSchool($schoolId)->with(['class', 'subject'])->get();
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return view('school-admin.timetable.index', compact(
            'classes',
            'academicYears',
            'selectedYearId',
            'selectedSectionId',
            'entries',
            'classSubjects',
            'weekdays'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'academic_year_id' => ['required', SchoolAccess::existsInSchool('academic_years', $schoolId)],
            'section_id' => ['required', SchoolAccess::existsInSchool('sections', $schoolId)],
            'class_subject_id' => [
                'required',
                SchoolAccess::existsInSchool('class_subjects', $schoolId)
                    ->where('academic_year_id', $request->input('academic_year_id')),
            ],
            'weekday' => ['required', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'room_id' => ['nullable', 'string', 'max:50'],
        ]);

        // Check room overlap in the same school and weekday
        if (! empty($validated['room_id'])) {
            $roomOverlap = TimetableEntry::forSchool($schoolId)
                ->where('academic_year_id', $validated['academic_year_id'])
                ->where('weekday', $validated['weekday'])
                ->where('room_id', $validated['room_id'])
                ->where(function ($q) use ($validated) {
                    $q->whereBetween('starts_at', [$validated['starts_at'], $validated['ends_at']])
                        ->orWhereBetween('ends_at', [$validated['starts_at'], $validated['ends_at']]);
                })
                ->exists();

            if ($roomOverlap) {
                return back()->withErrors(['room_id' => 'Room '.$validated['room_id'].' is already booked for this time.'])->withInput();
            }
        }

        TimetableEntry::create([
            'school_id' => $schoolId,
            'academic_year_id' => $validated['academic_year_id'],
            'section_id' => $validated['section_id'],
            'class_subject_id' => $validated['class_subject_id'],
            'weekday' => $validated['weekday'],
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'],
            'room_id' => $validated['room_id'] ?? null,
        ]);

        return back()->with('status', 'Timetable slot added successfully.');
    }
}
