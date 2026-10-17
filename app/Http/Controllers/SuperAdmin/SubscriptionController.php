<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Services\AuditLogService;
use App\Support\AnyFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Subscription::with(['school', 'plan', 'payments']);

        AnyFilter::apply($query, [
            $request->filled('status') ? fn (Builder $filter) => $filter->where('status', $request->status) : null,
            $request->filled('plan_id') ? fn (Builder $filter) => $filter->where('plan_id', $request->plan_id) : null,
        ]);

        $subscriptions = $query->latest()->paginate(25)->withQueryString();
        $plans = Plan::where('is_active', true)->get();

        return view('super-admin.subscriptions.index', compact('subscriptions', 'plans'));
    }

    public function recordPayment(Request $request, Subscription $subscription): RedirectResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'provider' => ['required', 'string'],
            'provider_reference' => ['nullable', 'string'],
        ]);

        SubscriptionPayment::create([
            'subscription_id' => $subscription->id,
            'school_id' => $subscription->school_id,
            'amount' => $request->amount,
            'currency' => $subscription->plan->currency,
            'provider' => $request->provider,
            'provider_reference' => $request->provider_reference,
            'status' => 'completed',
            'paid_at' => now(),
        ]);

        $subscription->update([
            'status' => 'active',
            'ends_at' => ($subscription->ends_at && $subscription->ends_at->isFuture() ? $subscription->ends_at : now())->addMonth(),
        ]);

        AuditLogService::log('subscription_payment_recorded', 'Subscription', $subscription->id, [
            'amount' => $request->amount,
            'school_id' => $subscription->school_id,
        ], $subscription->school_id);

        return back()->with('status', 'Subscription payment recorded and validity extended.');
    }

    public function update(Request $request, Subscription $subscription): RedirectResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'status' => ['required', 'in:trial,active,past_due,expired,suspended,cancelled'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $old = $subscription->only(['plan_id', 'status', 'starts_at', 'ends_at']);

        $subscription->update($validated);

        AuditLogService::log('subscription_updated', 'Subscription', $subscription->id, [
            'old' => $old,
            'new' => $validated,
        ], $subscription->school_id);

        return back()->with('status', 'Subscription updated. School operational records were not deleted.');
    }
}
