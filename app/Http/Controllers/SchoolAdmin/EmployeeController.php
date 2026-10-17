<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\WhatsAppCredentialDeliveryService;
use App\Support\AnyFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): View|Response
    {
        $schoolId = session('current_school_id');
        $query = Employee::forSchool($schoolId)->with(['user', 'teacherAssignments.classSubject.subject']);

        $search = $request->filled('search') ? '%'.trim($request->search).'%' : null;

        AnyFilter::apply($query, [
            $search ? fn (Builder $filter) => $filter->where(function (Builder $fields) use ($search): void {
                $fields->where('name', 'like', $search)
                    ->orWhere('employee_number', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search);
            }) : null,
            $request->filled('role') ? fn (Builder $filter) => $filter->where('role', $request->role) : null,
            $request->filled('status') ? fn (Builder $filter) => $filter->where('status', $request->status) : null,
        ]);

        $employees = $query->latest()->paginate(25)->withQueryString();

        return response()
            ->view('school-admin.employees.index', compact('employees'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function create(): View
    {
        return view('school-admin.employees.create');
    }

    public function store(Request $request, WhatsAppCredentialDeliveryService $deliveryService): RedirectResponse
    {
        $schoolId = session('current_school_id');

        $validated = $request->validate([
            'employee_number' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'job_title' => ['required', 'string', 'max:100'],
            'role' => ['required', 'in:principal,teacher,accountant,staff'],
            'salary' => ['required', 'numeric', 'min:0'],
            'joining_date' => ['required', 'date'],
            'give_system_access' => ['nullable', 'boolean'],
            'whatsapp_opt_in' => ['sometimes', 'accepted'],
        ]);

        if (auth()->user()->schoolRole((int) $schoolId) === 'principal' && $validated['role'] === 'principal') {
            throw ValidationException::withMessages([
                'role' => 'Only a school administrator can create a principal account.',
            ]);
        }

        if (! empty($validated['employee_number'])) {
            $exists = Employee::forSchool($schoolId)->where('employee_number', $validated['employee_number'])->exists();
            if ($exists) {
                return back()->withErrors(['employee_number' => 'Employee number already in use.'])->withInput();
            }
        }

        $temporaryPassword = null;
        $employee = DB::transaction(function () use ($schoolId, $validated, &$temporaryPassword) {
            $subscription = Subscription::with('plan')
                ->where('school_id', $schoolId)
                ->lockForUpdate()
                ->latest()
                ->first();

            if ($subscription && Employee::forSchool($schoolId)->count() >= $subscription->plan->staff_limit) {
                throw ValidationException::withMessages([
                    'staff_limit' => 'The staff limit for this school subscription has been reached.',
                ]);
            }

            // Auto-generate employee_number if not provided
            if (empty($validated['employee_number'])) {
                $prefix = 'EMP-'.$schoolId.'-';
                $lastEmployee = Employee::forSchool($schoolId)
                    ->where('employee_number', 'like', $prefix.'%')
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();
                $lastId = $lastEmployee ? $lastEmployee->id : 0;
                $validated['employee_number'] = $prefix.str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            }

            $user = null;
            if (! empty($validated['give_system_access'])) {
                $user = User::where('email', strtolower($validated['email']))->first();

                if ($user && ($user->isSuperAdmin() || $user->status !== 'active')) {
                    throw ValidationException::withMessages([
                        'email' => 'This email belongs to an unavailable account.',
                    ]);
                }

                if ($user && Employee::forSchool($schoolId)->where('user_id', $user->id)->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'This account is already linked to an employee in this school.',
                    ]);
                }

                if (! $user) {
                    $temporaryPassword = Str::random(40);
                    $user = User::create([
                        'name' => $validated['name'],
                        'email' => strtolower($validated['email']),
                        'password' => $temporaryPassword,
                        'must_change_password' => true,
                        'is_super_admin' => false,
                        'status' => 'active',
                        'email_verified_at' => now(),
                    ]);
                }

                $membership = $user->schools()
                    ->where('schools.id', $schoolId)
                    ->first();
                if ($membership && (
                    $membership->pivot->status !== 'active'
                    || $membership->pivot->role !== $validated['role']
                )) {
                    throw ValidationException::withMessages([
                        'email' => 'This account already has a different or inactive role in this school.',
                    ]);
                }

                if (! $membership) {
                    $user->schools()->syncWithoutDetaching([
                        $schoolId => [
                            'role' => $validated['role'],
                            'status' => 'active',
                            'joined_at' => now(),
                        ],
                    ]);
                }
            }

            $employee = Employee::create([
                'school_id' => $schoolId,
                'user_id' => $user?->id,
                'employee_number' => $validated['employee_number'],
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'job_title' => $validated['job_title'],
                'role' => $validated['role'],
                'salary' => $validated['salary'],
                'joining_date' => $validated['joining_date'],
                'status' => 'active',
            ]);

            AuditLogService::log('employee_created', 'Employee', $employee->id, [
                'name' => $employee->name,
                'role' => $employee->role,
            ], $schoolId);

            return $employee;
        });

        $response = redirect()->route('school-admin.employees.index')
            ->with('status', 'Employee profile created successfully.');

        if ($temporaryPassword && $employee->user_id) {
            $whatsAppStatus = 'WhatsApp delivery was not requested; no message was sent.';
            if (($missing = $deliveryService->missingConfiguration()) !== []) {
                $whatsAppStatus = 'WhatsApp message not queued: missing configuration for '.implode(', ', $missing).'.';
            } elseif (! empty($validated['whatsapp_opt_in']) && filled($validated['phone'])) {
                if (! $deliveryService->isValidRecipient($validated['phone'])) {
                    $whatsAppStatus = 'WhatsApp message not queued: enter the number in international format, such as +15551234567.';
                } elseif ($deliveryService->queueCredentials(
                    (int) $schoolId,
                    (int) $employee->user_id,
                    $validated['phone'],
                    strtolower($validated['email']),
                    $temporaryPassword
                )) {
                    $whatsAppStatus = 'WhatsApp credential message queued; delivery is not yet confirmed.';
                }
            } elseif (! empty($validated['whatsapp_opt_in'])) {
                $whatsAppStatus = 'WhatsApp message not queued: a phone number is required.';
            }

            $response->with('account_credentials', [
                'school_id' => (int) $schoolId,
                'username' => strtolower($validated['email']),
                'encrypted_password' => Crypt::encryptString($temporaryPassword),
                'whatsapp_status' => $whatsAppStatus,
            ]);
        }

        return $response;
    }
}
