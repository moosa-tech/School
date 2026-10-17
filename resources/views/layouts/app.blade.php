<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'School Management SaaS' }}</title>
    <link rel="icon" type="image/png" sizes="64x64" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        @media (max-width: 767px) {
            main { min-width: 0; }
            main table {
                display: block;
                width: max-content;
                min-width: 100%;
                max-width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            main pre { max-width: 100%; overflow-x: auto; }
            main img, main video, main canvas, main svg { max-width: 100%; }
        }
        @media (max-width: 639px) {
            main .grid.grid-cols-3 { grid-template-columns: minmax(0, 1fr) !important; }
        }
        @media (max-width: 359px) {
            main .grid.grid-cols-2 { grid-template-columns: minmax(0, 1fr) !important; }
        }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">
    @php
        $currentUser = auth()->user();
        $currentRole = $currentUser?->isSuperAdmin() ? 'super_admin' : $currentUser?->schoolRole();
        $dashboardRoute = match ($currentRole) {
            'super_admin' => route('super-admin.dashboard'),
            'teacher' => route('teacher.dashboard'),
            'student' => route('student.dashboard'),
            'parent' => route('parent.dashboard'),
            'school_admin', 'principal', 'accountant', 'staff' => route('school-admin.dashboard'),
            default => $currentUser ? route('profile.edit') : route('login'),
        };
    @endphp

    <!-- Top Navigation Bar -->
    <nav class="bg-indigo-900 text-white shadow-md no-print sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <a href="{{ $dashboardRoute }}" class="flex items-center space-x-2 font-bold text-lg tracking-wide hover:text-indigo-200 transition">
                        <i class="fa-solid fa-graduation-cap text-2xl text-amber-400"></i>
                        <span class="text-base sm:text-lg">EduSaaS <span class="ml-1 hidden rounded bg-indigo-800 px-2 py-0.5 text-xs font-normal text-indigo-200 sm:inline">Multi-School</span></span>
                    </a>

                    @if(session('current_school_id') && ($currentSchool = \App\Models\School::find(session('current_school_id'))))
                        <div class="hidden md:flex items-center text-xs bg-indigo-950 text-indigo-200 px-3 py-1.5 rounded-full border border-indigo-700">
                            <i class="fa-solid fa-school mr-1.5 text-amber-400"></i>
                            <span class="font-semibold text-white mr-1">{{ $currentSchool->name }}</span>
                            <span class="text-indigo-300">({{ ucfirst($currentSchool->status) }})</span>
                        </div>
                    @endif
                </div>

                <!-- User Dropdown & Logout -->
                <div class="flex items-center space-x-4">
                    @auth
                        <div class="flex items-center space-x-3">
                            <div class="text-right hidden sm:block">
                                <div class="text-sm font-semibold text-white leading-tight">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-indigo-300">
                                    @if(auth()->user()->isSuperAdmin())
                                        <span class="bg-purple-900 text-purple-200 px-1.5 py-0.5 rounded text-[10px] font-bold">Super Admin</span>
                                    @else
                                        <span class="capitalize">{{ auth()->user()->schoolRole() ?? 'Member' }}</span>
                                    @endif
                                </div>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded px-2 py-1 text-xs text-indigo-300 transition hover:text-white" title="Account Settings">
                                <i class="fa-solid fa-user-gear mr-1"></i>
                                <span class="hidden sm:inline">Settings</span>
                            </a>
                            <form action="{{ route('logout') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="bg-indigo-800 hover:bg-rose-700 text-white text-xs px-3 py-2 rounded-lg font-medium transition flex items-center space-x-1 shadow-sm" title="Log out">
                                    <i class="fa-solid fa-right-from-bracket"></i>
                                    <span class="hidden md:inline">Logout</span>
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Workspace with Sidebar -->
    <div x-data="{ sidebarOpen: false }" x-on:keydown.escape.window="sidebarOpen = false"
        class="flex-1 flex max-w-7xl w-full min-w-0 mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 gap-4 sm:gap-6">

        @auth
        <button type="button" x-cloak x-show="sidebarOpen" x-on:click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-slate-900/50 md:hidden" aria-label="Close navigation menu"></button>

        <!-- Navigation Sidebar (Tenant or SuperAdmin) -->
        <aside id="app-sidebar"
            class="fixed inset-y-16 left-0 z-40 w-72 max-w-[85vw] -translate-x-full overflow-y-auto no-print transition-transform md:sticky md:top-20 md:h-[calc(100vh-6rem)] md:w-64 md:max-w-none md:shrink-0 md:translate-x-0"
            x-bind:style="sidebarOpen ? 'transform: translateX(0)' : null">
            <div class="min-h-full rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:min-h-0">
                <div class="mb-4 flex justify-end md:hidden">
                    <button type="button" x-on:click="sidebarOpen = false"
                        class="rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                        aria-label="Close navigation menu">
                        <i class="fa-solid fa-xmark mr-1"></i> Close
                    </button>
                </div>
                <div class="space-y-6">

                @if(auth()->user()->isSuperAdmin())
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">Platform Master</div>
                        <nav class="space-y-1">
                            <a href="{{ route('super-admin.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('super-admin.dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-chart-pie w-5 mr-2 text-indigo-600"></i> Dashboard
                            </a>
                            <a href="{{ route('super-admin.schools.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('super-admin.schools.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-school-flag w-5 mr-2 text-indigo-600"></i> Schools Directory
                            </a>
                            <a href="{{ route('super-admin.plans.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('super-admin.plans.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-layer-group w-5 mr-2 text-indigo-600"></i> Plans & Pricing
                            </a>
                            <a href="{{ route('super-admin.subscriptions.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('super-admin.subscriptions.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-file-invoice-dollar w-5 mr-2 text-indigo-600"></i> Subscriptions
                            </a>
                            <a href="{{ route('super-admin.audit-logs.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('super-admin.audit-logs.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-shield-halved w-5 mr-2 text-indigo-600"></i> Audit & Security
                            </a>
                        </nav>
                    </div>
                @endif

                @if(session('current_school_id') && ! $currentUser?->isSuperAdmin() && in_array($currentRole, ['school_admin', 'principal', 'accountant', 'staff'], true))
                    <!-- School Operations -->
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">School Operations</div>
                        <nav class="space-y-1">
                            <a href="{{ route('school-admin.dashboard') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-gauge-high w-5 mr-2 text-indigo-600"></i> School Overview
                            </a>
                            @if(in_array($currentRole, ['school_admin', 'principal'], true))
                                <a href="{{ route('school-admin.students.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.students.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <i class="fa-solid fa-user-graduate w-5 mr-2 text-indigo-600"></i> Students & Admissions
                                </a>
                                <a href="{{ route('school-admin.employees.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.employees.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <i class="fa-solid fa-chalkboard-user w-5 mr-2 text-indigo-600"></i> Staff & Faculty
                                </a>
                            @endif
                            @if(in_array($currentRole, ['school_admin', 'principal', 'teacher', 'staff'], true))
                                <a href="{{ route('school-admin.attendance.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.attendance.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <i class="fa-solid fa-clipboard-user w-5 mr-2 text-indigo-600"></i> Attendance Roll Call
                                </a>
                                <a href="{{ route('school-admin.attendance.register') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.attendance.register') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                    <i class="fa-solid fa-calendar-days w-5 mr-2 text-indigo-600"></i> Student Monthly Register
                                </a>
                                @if(in_array($currentRole, ['school_admin', 'principal'], true))
                                    <a href="{{ route('school-admin.staff-attendance.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.staff-attendance.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                        <i class="fa-solid fa-user-clock w-5 mr-2 text-indigo-600"></i> Teacher Attendance
                                    </a>
                                @endif
                            @endif
                        </nav>
                    </div>

                    <!-- Finance & Billing -->
                    @if(in_array($currentRole, ['school_admin', 'principal', 'accountant'], true))
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">Fees & Billing</div>
                        <nav class="space-y-1">
                            <a href="{{ route('school-admin.fees.invoices') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.fees.invoices*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-file-invoice w-5 mr-2 text-indigo-600"></i> Fee Invoices
                            </a>
                            <a href="{{ route('school-admin.fees.payments') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.fees.payments*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-receipt w-5 mr-2 text-indigo-600"></i> Payments & Receipts
                            </a>
                            <a href="{{ route('school-admin.fees.structures') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.fees.structures*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-sliders w-5 mr-2 text-indigo-600"></i> Fee Structures
                            </a>
                        </nav>
                    </div>
                    @endif

                    <!-- Academic Management -->
                    @if(in_array($currentRole, ['school_admin', 'principal'], true))
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">Academics</div>
                        <nav class="space-y-1">
                            <a href="{{ route('school-admin.academic-years.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.academic-years.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-calendar-days w-5 mr-2 text-indigo-600"></i> Academic Years
                            </a>
                            <a href="{{ route('school-admin.classes.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.classes.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-shapes w-5 mr-2 text-indigo-600"></i> Classes & Sections
                            </a>
                            <a href="{{ route('school-admin.subjects.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.subjects.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-book-open w-5 mr-2 text-indigo-600"></i> Subjects Catalog
                            </a>
                            <a href="{{ route('school-admin.exams.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.exams.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-file-lines w-5 mr-2 text-indigo-600"></i> Exams & Marks
                            </a>
                            <a href="{{ route('school-admin.timetable.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.timetable.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-clock w-5 mr-2 text-indigo-600"></i> Timetable
                            </a>
                            <a href="{{ route('school-admin.homework.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.homework.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-book-bookmark w-5 mr-2 text-indigo-600"></i> Homework
                            </a>
                            <a href="{{ route('school-admin.announcements.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.announcements.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-bullhorn w-5 mr-2 text-indigo-600"></i> Notices
                            </a>
                            <a href="{{ route('school-admin.reports.index') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg {{ request()->routeIs('school-admin.reports.*') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50' }}">
                                <i class="fa-solid fa-chart-column w-5 mr-2 text-indigo-600"></i> Reports & Exports
                            </a>
                        </nav>
                    </div>
                    @endif
                @elseif(in_array($currentRole, ['teacher', 'student'], true))
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider px-3 mb-2">My Portal</div>
                        <nav class="space-y-1">
                            <a href="{{ $dashboardRoute }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg bg-indigo-50 text-indigo-700 font-semibold">
                                <i class="fa-solid fa-gauge-high w-5 mr-2 text-indigo-600"></i> Dashboard
                            </a>
                            @if($currentRole === 'teacher')
                                <a href="{{ route('school-admin.attendance.index') }}" class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                                    <i class="fa-solid fa-clipboard-user mr-2 w-5 text-indigo-600"></i> Assigned Class Attendance
                                </a>
                                <a href="{{ route('school-admin.attendance.register') }}" class="flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                                    <i class="fa-solid fa-calendar-days mr-2 w-5 text-indigo-600"></i> Student Monthly Register
                                </a>
                            @endif
                        </nav>
                    </div>
                @endif

                <div class="space-y-1 border-t border-slate-100 pt-4">
                    <a href="{{ route('profile.edit') }}" class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->routeIs('profile.edit', 'profile.update') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-indigo-600' }}">
                        <i class="fa-solid fa-user-gear mr-2 w-5 text-indigo-500"></i>
                        <span>Account Settings</span>
                    </a>
                    <a href="{{ route('profile.password') }}" class="flex items-center px-3 py-2 text-sm font-medium rounded-lg text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition">
                        <i class="fa-solid fa-key w-5 mr-2 text-amber-500"></i>
                        <span>Change Password</span>
                    </a>
                </div>

                </div>
            </div>
        </aside>
        @endauth

        <!-- Main Content Area -->
        <main class="flex-1 min-w-0">
            @auth
                <div class="mb-4 md:hidden">
                    <button type="button" x-on:click="sidebarOpen = true" aria-controls="app-sidebar" x-bind:aria-expanded="sidebarOpen"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        <i class="fa-solid fa-bars text-indigo-600"></i>
                        Menu
                    </button>
                </div>
            @endauth

            <!-- Flash Message Banner -->
            @if(session('status'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm rounded-xl p-4 flex items-center space-x-3 shadow-sm">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 text-sm rounded-xl p-4 space-y-1 shadow-sm">
                    <div class="flex items-center space-x-2 font-semibold">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                        <span>{{ session('error') ?? 'Please correct the highlighted issues:' }}</span>
                    </div>
                    @if($errors->any())
                        <ul class="list-disc list-inside text-xs space-y-0.5 pl-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

</body>
</html>
