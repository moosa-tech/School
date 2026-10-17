<nav aria-label="Student portal" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
    <a href="{{ route('student.profile') }}" class="rounded-xl border border-slate-200 bg-white p-4 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50">
        <i class="fa-solid fa-id-card mr-2"></i>My Profile
    </a>
    <a href="{{ route('student.attendance') }}" class="rounded-xl border border-slate-200 bg-white p-4 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50">
        <i class="fa-solid fa-calendar-check mr-2"></i>Attendance
    </a>
    <a href="{{ route('student.fees') }}" class="rounded-xl border border-slate-200 bg-white p-4 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50">
        <i class="fa-solid fa-receipt mr-2"></i>Fee History
    </a>
    <a href="{{ route('student.results') }}" class="rounded-xl border border-slate-200 bg-white p-4 text-sm font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50">
        <i class="fa-solid fa-square-poll-vertical mr-2"></i>Results
    </a>
</nav>
