<?php

use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ParentPortal\ParentDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolAdmin\AcademicYearController;
use App\Http\Controllers\SchoolAdmin\AnnouncementController;
use App\Http\Controllers\SchoolAdmin\AttendanceController;
use App\Http\Controllers\SchoolAdmin\ClassController;
use App\Http\Controllers\SchoolAdmin\DashboardController as SchoolDashboardController;
use App\Http\Controllers\SchoolAdmin\EmployeeController;
use App\Http\Controllers\SchoolAdmin\ExamController;
use App\Http\Controllers\SchoolAdmin\ExamMarksImportController;
use App\Http\Controllers\SchoolAdmin\FeeController;
use App\Http\Controllers\SchoolAdmin\HomeworkController;
use App\Http\Controllers\SchoolAdmin\ReportController;
use App\Http\Controllers\SchoolAdmin\SectionController;
use App\Http\Controllers\SchoolAdmin\StaffAttendanceController;
use App\Http\Controllers\SchoolAdmin\StudentController;
use App\Http\Controllers\SchoolAdmin\StudentQrController;
use App\Http\Controllers\SchoolAdmin\SubjectController;
use App\Http\Controllers\SchoolAdmin\TimetableController;
use App\Http\Controllers\StudentPortal\StudentDashboardController;
use App\Http\Controllers\SuperAdmin\AuditLogController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperDashboardController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\SuperAdmin\PlatformUserController;
use App\Http\Controllers\SuperAdmin\SchoolController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', fn () => redirect()->route('login'));

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
    Route::get('/invitations/accept/{token}', [InvitationController::class, 'showAcceptForm'])->name('invitations.accept.form');
    Route::post('/invitations/accept/{token}', [InvitationController::class, 'accept'])->name('invitations.accept.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Profile and account settings are scoped to the signed-in account.
Route::middleware(['auth', 'password.change'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/photo', [ProfileController::class, 'showPhoto'])->name('profile.photo');
    Route::get('/profile/password', [ProfileController::class, 'showChangePassword'])->name('profile.password');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])
        ->middleware('throttle:6,1')
        ->name('profile.password.update');
});

Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])
    ->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'receive'])
    ->name('webhooks.whatsapp.receive');

// Super Admin Platform Routes
Route::prefix('super-admin')
    ->name('super-admin.')
    ->middleware(['auth', 'password.change', 'super_admin'])
    ->group(function () {
        Route::get('/dashboard', [SuperDashboardController::class, 'index'])->name('dashboard');
        Route::get('/schools', [SchoolController::class, 'index'])->name('schools.index');
        Route::get('/schools/create', [SchoolController::class, 'create'])->name('schools.create');
        Route::post('/schools', [SchoolController::class, 'store'])->name('schools.store');
        Route::get('/schools/{school}', [SchoolController::class, 'show'])->name('schools.show');
        Route::get('/schools/{school}/edit', [SchoolController::class, 'edit'])->name('schools.edit');
        Route::patch('/schools/{school}', [SchoolController::class, 'update'])->name('schools.update');
        Route::post('/schools/{school}/status', [SchoolController::class, 'updateStatus'])->name('schools.status');
        Route::post('/schools/{school}/invite', [SchoolController::class, 'invite'])->name('schools.invite');
        Route::post('/schools/{school}/switch', [SchoolController::class, 'switchContext'])->name('schools.switch');
        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
        Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::patch('/subscriptions/{subscription}', [SubscriptionController::class, 'update'])->name('subscriptions.update');
        Route::post('/subscriptions/{subscription}/payment', [SubscriptionController::class, 'recordPayment'])->name('subscriptions.payment');
        Route::get('/platform-users', [PlatformUserController::class, 'index'])->name('platform-users.index');
        Route::post('/platform-users', [PlatformUserController::class, 'store'])->name('platform-users.store');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

// School Staff Routes (school_admin, principal, accountant, teacher, staff)
Route::prefix('school-admin')
    ->name('school-admin.')
    ->middleware(['auth', 'password.change', 'school_tenant', 'active_sub'])
    ->group(function () {
        // Dashboard - restricted to administrator/staff roles; teacher and student access goes through their dedicated portals
        Route::middleware('school_tenant:school_admin|principal|accountant|staff')->group(function () {
            Route::get('/dashboard', [SchoolDashboardController::class, 'index'])->name('dashboard');
        });

        // Admin & Principal access
        Route::middleware('school_tenant:school_admin|principal')->group(function () {
            Route::get('/academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
            Route::post('/academic-years', [AcademicYearController::class, 'store'])->name('academic-years.store');
            Route::post('/academic-years/{academicYear}/set-current', [AcademicYearController::class, 'setCurrent'])->name('academic-years.set-current');
            Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
            Route::post('/classes', [ClassController::class, 'store'])->name('classes.store');
            Route::post('/sections', [SectionController::class, 'store'])->name('sections.store');
            Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
            Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
            Route::post('/subjects/assign', [SubjectController::class, 'assignToClass'])->name('subjects.assign');
            Route::post('/subjects/{classSubject}/teacher', [SubjectController::class, 'updateTeacherAssignment'])->name('subjects.teacher.update');
            Route::get('/students', [StudentController::class, 'index'])->name('students.index');
            Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
            Route::post('/students', [StudentController::class, 'store'])->name('students.store');
            Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
            Route::post('/students/{student}/status', [StudentController::class, 'updateStatus'])->name('students.status');
            Route::get('/students/{student}/qr-card', [StudentQrController::class, 'card'])->name('students.qr-card');
            Route::post('/students/{student}/qr-token/rotate', [StudentQrController::class, 'rotateToken'])->name('students.qr-token.rotate');
            Route::delete('/students/{student}/qr-token', [StudentQrController::class, 'revokeToken'])->name('students.qr-token.revoke');
            Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
            Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
            Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
            Route::get('/timetable', [TimetableController::class, 'index'])->name('timetable.index');
            Route::post('/timetable', [TimetableController::class, 'store'])->name('timetable.store');
        });

        // Admin only
        Route::middleware('school_tenant:school_admin')->group(function () {
            Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('/reports/student-register', [ReportController::class, 'studentRegister'])->name('reports.student-register');
            Route::get('/reports/fee-collection', [ReportController::class, 'feeCollection'])->name('reports.fee-collection');
            Route::get('/reports/outstanding-dues', [ReportController::class, 'outstandingDues'])->name('reports.outstanding-dues');
            Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
            Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
        });

        // Attendance (teacher & above)
        Route::middleware('school_tenant:school_admin|principal|teacher|staff')->group(function () {
            Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
            Route::post('/attendance/roll-call', [AttendanceController::class, 'saveRollCall'])->name('attendance.save');
            Route::get('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
            Route::get('/attendance/register', [AttendanceController::class, 'register'])->name('attendance.register');
            Route::get('/attendance/qr-scanner', [StudentQrController::class, 'scanner'])->name('attendance.qr-scanner');
            Route::post('/attendance/qr-scan', [StudentQrController::class, 'record'])->middleware('throttle:60,1')->name('attendance.qr-scan');
            Route::post('/attendance/qr-manual', [StudentQrController::class, 'manual'])->middleware('throttle:30,1')->name('attendance.qr-manual');
        });

        Route::middleware('school_tenant:school_admin|principal')->group(function () {
            Route::get('/staff-attendance', [StaffAttendanceController::class, 'index'])->name('staff-attendance.index');
            Route::post('/staff-attendance', [StaffAttendanceController::class, 'save'])->name('staff-attendance.save');
            Route::get('/staff-attendance/register', [StaffAttendanceController::class, 'register'])->name('staff-attendance.register');
        });

        // Fees (admin, principal, accountant)
        Route::middleware('school_tenant:school_admin|principal|accountant')->group(function () {
            Route::get('/fees/invoices', [FeeController::class, 'invoices'])->name('fees.invoices');
            Route::get('/fees/invoices/create', [FeeController::class, 'createInvoice'])->name('fees.invoices.create');
            Route::post('/fees/invoices', [FeeController::class, 'storeInvoice'])->name('fees.invoices.store');
            Route::post('/fees/invoices/batch', [FeeController::class, 'generateBatchInvoices'])->name('fees.invoices.batch');
            Route::get('/fees/invoices/{invoice}', [FeeController::class, 'showInvoice'])->name('fees.invoices.show');
            Route::post('/fees/invoices/{invoice}/pay', [FeeController::class, 'recordPayment'])->name('fees.invoices.pay');
            Route::get('/fees/payments', [FeeController::class, 'payments'])->name('fees.payments');
            Route::get('/fees/payments/{payment}/receipt', [FeeController::class, 'showReceipt'])->name('fees.receipt');
            Route::get('/fees/structures', [FeeController::class, 'structures'])->name('fees.structures');
            Route::post('/fees/categories', [FeeController::class, 'storeCategory'])->name('fees.categories.store');
            Route::post('/fees/structures', [FeeController::class, 'storeStructure'])->name('fees.structures.store');
            Route::post('/fees/adjustments', [FeeController::class, 'storeAdjustment'])->name('fees.adjustments.store');
        });

        // Exams & Marks (admin, principal, teacher)
        Route::middleware('school_tenant:school_admin|principal|teacher')->group(function () {
            Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
            Route::post('/exams', [ExamController::class, 'store'])
                ->middleware('school_tenant:school_admin|principal')
                ->name('exams.store');
            Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
            Route::post('/exams/{exam}/subjects', [ExamController::class, 'addSubject'])
                ->middleware('school_tenant:school_admin|principal')
                ->name('exams.subjects.add');
            Route::post('/exams/{exam}/toggle-publish', [ExamController::class, 'togglePublish'])
                ->middleware('school_tenant:school_admin|principal')
                ->name('exams.toggle-publish');
            Route::get('/exam-subjects/{examSubject}/marks', [ExamController::class, 'marksEntry'])->name('exams.marks.entry');
            Route::post('/exam-subjects/{examSubject}/marks', [ExamController::class, 'saveMarks'])->name('exams.marks.save');
            Route::get('/exam-subjects/{examSubject}/marks/import', [ExamMarksImportController::class, 'create'])->name('exams.marks.import');
            Route::get('/exam-subjects/{examSubject}/marks/template', [ExamMarksImportController::class, 'downloadTemplate'])->name('exams.marks.template');
            Route::post('/exam-subjects/{examSubject}/marks/import/preview', [ExamMarksImportController::class, 'preview'])->name('exams.marks.import.preview');
            Route::post('/exam-subjects/{examSubject}/marks/import/confirm', [ExamMarksImportController::class, 'confirm'])->name('exams.marks.import.confirm');
            Route::post('/exam-subjects/{examSubject}/marks/import/errors', [ExamMarksImportController::class, 'downloadErrors'])->name('exams.marks.import.errors');
            Route::get('/exam-subjects/{examSubject}/marks/export', [ExamMarksImportController::class, 'export'])->name('exams.marks.export');
            Route::get('/exams/{exam}/students/{student}/report-card', [ExamController::class, 'reportCard'])
                ->middleware('school_tenant:school_admin|principal')
                ->name('exams.report-card');
        });

        // Homework (teacher & admin)
        Route::middleware('school_tenant:school_admin|principal|teacher')->group(function () {
            Route::get('/homework', [HomeworkController::class, 'index'])->name('homework.index');
            Route::post('/homework', [HomeworkController::class, 'store'])->name('homework.store');
        });
    });

// Teacher Portal Routes
Route::prefix('teacher')
    ->name('teacher.')
    ->middleware(['auth', 'password.change', 'school_tenant:teacher|school_admin|principal', 'active_sub'])
    ->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    });

// Parent Portal Routes
Route::prefix('parent')
    ->name('parent.')
    ->middleware(['auth', 'password.change', 'school_tenant:parent', 'active_sub'])
    ->group(function () {
        Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
    });

// Student Portal Routes
Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'password.change', 'school_tenant:student', 'active_sub'])
    ->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [StudentDashboardController::class, 'profile'])->name('profile');
        Route::get('/attendance', [StudentDashboardController::class, 'attendance'])->name('attendance');
        Route::get('/fees', [StudentDashboardController::class, 'fees'])->name('fees');
        Route::get('/fees/receipts/{payment}', [StudentDashboardController::class, 'receipt'])->name('fees.receipt');
        Route::get('/results', [StudentDashboardController::class, 'results'])->name('results');
    });
