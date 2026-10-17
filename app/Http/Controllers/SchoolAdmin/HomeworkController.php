<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\ClassSubject;
use App\Models\Homework;
use App\Support\SchoolAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeworkController extends Controller
{
    public function index(): View
    {
        $schoolId = session('current_school_id');
        $homeworkList = Homework::forSchool($schoolId)->with(['classSubject.class', 'classSubject.subject', 'creator'])->latest('due_on')->paginate(25);
        $classSubjects = ClassSubject::forSchool($schoolId)->with(['class', 'subject'])->get();

        return view('school-admin.homework.index', compact('homeworkList', 'classSubjects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'class_subject_id' => ['required', SchoolAccess::existsInSchool('class_subjects', $schoolId)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'assigned_on' => ['required', 'date'],
            'due_on' => ['required', 'date', 'after_or_equal:assigned_on'],
        ]);

        Homework::create([
            'school_id' => $schoolId,
            'class_subject_id' => $validated['class_subject_id'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'assigned_on' => $validated['assigned_on'],
            'due_on' => $validated['due_on'],
            'created_by' => auth()->id(),
        ]);

        return back()->with('status', 'Homework assigned successfully.');
    }
}
