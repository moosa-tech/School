<?php

namespace App\Providers;

use App\Models\AttendanceSession;
use App\Models\Employee;
use App\Models\Exam;
use App\Models\FeeInvoice;
use App\Models\Student;
use App\Policies\AttendancePolicy;
use App\Policies\EmployeePolicy;
use App\Policies\ExamPolicy;
use App\Policies\FeeInvoicePolicy;
use App\Policies\StudentPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(FeeInvoice::class, FeeInvoicePolicy::class);
        Gate::policy(AttendanceSession::class, AttendancePolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Exam::class, ExamPolicy::class);
    }
}
