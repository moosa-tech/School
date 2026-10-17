<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Models\Employee;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Support\SchoolAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $schoolId = session('current_school_id');
        $subjects = Subject::forSchool($schoolId)->get();
        $classes = SchoolClass::forSchool($schoolId)->get();
        $teachers = Employee::forSchool($schoolId)->where('role', 'teacher')->get();
        $currentYear = AcademicYear::forSchool($schoolId)->where('is_current', true)->first();

        $classSubjects = ClassSubject::forSchool($schoolId)
            ->with(['class', 'subject', 'teacher', 'teacherAssignments'])
            ->get();

        return view('school-admin.subjects.index', compact('subjects', 'classes', 'teachers', 'currentYear', 'classSubjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['nullable', 'string', 'max:30'],
        ]);

        Subject::create([
            'school_id' => $schoolId,
            'name' => $validated['name'],
            'code' => $validated['code'],
            'status' => 'active',
        ]);

        return back()->with('status', "Subject '{$validated['name']}' added to catalogue.");
    }

    public function assignToClass(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'academic_year_id' => ['required', SchoolAccess::existsInSchool('academic_years', $schoolId)],
            'class_id' => ['required', SchoolAccess::existsInSchool('classes', $schoolId)],
            'subject_id' => ['required', SchoolAccess::existsInSchool('subjects', $schoolId)],
            'employee_id' => [
                'nullable',
                Rule::exists('employees', 'id')->where(fn ($query) => $query
                    ->where('school_id', $schoolId)
                    ->where('role', 'teacher')
                    ->where('status', 'active')
                    ->whereNotNull('user_id')),
            ],
        ]);

        $class = SchoolClass::forSchool($schoolId)->findOrFail($validated['class_id']);
        $academicYear = AcademicYear::forSchool($schoolId)->findOrFail($validated['academic_year_id']);
        $subject = Subject::forSchool($schoolId)->findOrFail($validated['subject_id']);
        $teacher = ! empty($validated['employee_id'])
            ? Employee::forSchool($schoolId)->findOrFail($validated['employee_id'])
            : null;

        DB::transaction(function () use ($schoolId, $class, $academicYear, $subject, $teacher): void {
            $classSubject = ClassSubject::forSchool($schoolId)->firstOrNew([
                'academic_year_id' => $academicYear->id,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
            ]);
            $classSubject->school_id = $schoolId;
            $classSubject->save();

            $this->syncTeacherAssignment($classSubject, $teacher);
        });

        return back()->with('status', 'Subject assigned to class successfully.');
    }

    public function updateTeacherAssignment(Request $request, int $classSubjectId): RedirectResponse
    {
        $schoolId = session('current_school_id');
        $classSubject = ClassSubject::forSchool($schoolId)->findOrFail($classSubjectId);
        $validated = $request->validate([
            'employee_id' => [
                'nullable',
                Rule::exists('employees', 'id')->where(fn ($query) => $query
                    ->where('school_id', $schoolId)
                    ->where('role', 'teacher')
                    ->where('status', 'active')
                    ->whereNotNull('user_id')),
            ],
        ]);
        $teacher = ! empty($validated['employee_id'])
            ? Employee::forSchool($schoolId)->findOrFail($validated['employee_id'])
            : null;

        DB::transaction(function () use ($classSubject, $teacher): void {
            $this->syncTeacherAssignment($classSubject, $teacher);
        });

        return back()->with('status', $teacher
            ? 'Teacher assignment updated successfully.'
            : 'Teacher assignment removed successfully.');
    }

    private function syncTeacherAssignment(ClassSubject $classSubject, ?Employee $teacher): void
    {
        $classSubject->teacher_id = $teacher?->user_id;
        $classSubject->save();

        TeacherAssignment::forSchool($classSubject->school_id)
            ->where('class_subject_id', $classSubject->id)
            ->delete();

        if ($teacher) {
            TeacherAssignment::create([
                'school_id' => $classSubject->school_id,
                'employee_id' => $teacher->id,
                'class_subject_id' => $classSubject->id,
                'academic_year_id' => $classSubject->academic_year_id,
            ]);
        }
    }
}
