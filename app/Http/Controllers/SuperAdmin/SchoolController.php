<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Plan;
use App\Models\School;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\InvitationService;
use App\Support\AnyFilter;
use App\Support\SchoolAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function __construct(
        protected InvitationService $invitationService
    ) {}

    /**
     * List all schools with filters.
     */
    public function index(Request $request): View
    {
        $query = School::with(['currentSubscription.plan', 'creator']);

        $search = $request->filled('search') ? '%'.trim($request->search).'%' : null;

        AnyFilter::apply($query, [
            $search ? fn (Builder $filter) => $filter->where(function (Builder $fields) use ($search): void {
                $fields->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('slug', 'like', $search);
            }) : null,
            $request->filled('status') ? fn (Builder $filter) => $filter->where('status', $request->status) : null,
            $request->filled('plan_id') ? fn (Builder $filter) => $filter->whereHas('currentSubscription', fn (Builder $subscription) => $subscription->where('plan_id', $request->plan_id)) : null,
        ]);

        $schools = $query->latest()->paginate(25)->withQueryString();
        $plans = Plan::where('is_active', true)->get();

        return view('super-admin.schools.index', compact('schools', 'plans'));
    }

    /**
     * Show create school form.
     */
    public function create(): View
    {
        $plans = Plan::where('is_active', true)->get();

        return view('super-admin.schools.create', compact('plans'));
    }

    /**
     * Store a new school and provision its initial administrator account.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:100', 'unique:schools,slug'],
            'admin_email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'max:10'],
            'timezone' => ['required', 'string', 'max:50'],
            'plan_id' => ['required', 'exists:plans,id'],
            'is_trial' => ['nullable', 'boolean'],
        ]);

        $adminEmail = strtolower(trim($validated['admin_email']));
        if (User::query()->whereRaw('LOWER(email) = ?', [$adminEmail])->exists()) {
            throw ValidationException::withMessages([
                'admin_email' => 'An account already exists for this email address.',
            ]);
        }

        $baseSlug = filled($validated['slug'] ?? null)
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']).'-'.Str::random(4);

        $school = DB::transaction(function () use ($request, $validated, $adminEmail, $baseSlug) {
            $slug = $baseSlug;
            $suffix = 1;
            while (School::query()->where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            $school = School::create([
                'name' => $validated['name'],
                'slug' => $slug,
                'email' => $adminEmail,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'currency' => $validated['currency'],
                'timezone' => $validated['timezone'],
                'status' => 'active',
                'created_by' => auth()->id(),
            ]);

            $isTrial = $request->boolean('is_trial');
            Subscription::create([
                'school_id' => $school->id,
                'plan_id' => $validated['plan_id'],
                'status' => $isTrial ? 'trial' : 'active',
                'starts_at' => now(),
                'ends_at' => $isTrial ? now()->addDays(14) : now()->addMonth(),
                'trial_ends_at' => $isTrial ? now()->addDays(14) : null,
            ]);

            $admin = User::create([
                'name' => $school->name.' Admin',
                'email' => $adminEmail,
                'password' => $slug,
                'status' => 'active',
            ]);

            $admin->schools()->attach($school->id, [
                'role' => 'school_admin',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            AuditLogService::log('school_created', 'School', $school->id, [
                'name' => $school->name,
                'admin_email' => $adminEmail,
                'plan_id' => $validated['plan_id'],
            ], $school->id);

            return $school;
        });

        return redirect()->route('super-admin.schools.show', $school)
            ->with('status', "School '{$school->name}' created. The school admin password is the school slug.")
            ->with('initial_admin_email', $adminEmail)
            ->with('initial_admin_password', $school->slug);
    }

    /**
     * Show school details.
     */
    public function show(School $school): View
    {
        $school->load(['subscriptions.plan', 'invitations', 'users', 'currentSubscription.plan']);

        $overview = [
            'student_count' => Student::withoutGlobalScope('school')->where('school_id', $school->id)->count(),
            'teacher_count' => Employee::withoutGlobalScope('school')->where('school_id', $school->id)->where('role', 'teacher')->count(),
            'staff_count' => Employee::withoutGlobalScope('school')->where('school_id', $school->id)->count(),
            'admin_count' => $school->users()->wherePivot('role', 'school_admin')->count(),
        ];

        return view('super-admin.schools.show', compact('school', 'overview'));
    }

    /**
     * Update school status (e.g. suspend or activate).
     */
    public function updateStatus(Request $request, School $school): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:active,pending,suspended,archived'],
        ]);

        $oldStatus = $school->status;
        $school->update(['status' => $request->status]);

        AuditLogService::log('school_status_changed', 'School', $school->id, [
            'old' => $oldStatus,
            'new' => $request->status,
        ], $school->id);

        return back()->with('status', "School status updated to {$request->status}.");
    }

    /**
     * Support-only school context switch. Disabled unless explicitly enabled.
     */
    public function switchContext(School $school): RedirectResponse
    {
        if (! SchoolAccess::supportAccessEnabled()) {
            abort(403, 'Support access into school operations is disabled.');
        }

        AuditLogService::log('support_school_context_switch', 'School', $school->id, [
            'school' => $school->name,
        ], $school->id);

        session(['current_school_id' => $school->id]);

        return redirect()->route('school-admin.dashboard')
            ->with('status', "Support context switched to {$school->name}.");
    }

    public function invite(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'role' => ['required', 'in:school_admin,teacher,accountant,staff'],
        ]);

        $inviteData = $this->invitationService->issueInvitation(
            $school,
            $validated['email'],
            $validated['role'],
            auth()->user()
        );

        AuditLogService::log('school_invitation_issued', 'SchoolInvitation', $inviteData['invitation']->id, [
            'email' => $validated['email'],
            'role' => $validated['role'],
        ], $school->id);

        return back()
            ->with('status', 'Invitation created. Email delivery is not configured; copy the link below.')
            ->with('invitation_url', $inviteData['url']);
    }
}
