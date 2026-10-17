<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::withCount('subscriptions')->get();

        return view('super-admin.plans.index', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:10'],
            'billing_interval' => ['required', 'in:monthly,yearly'],
            'student_limit' => ['required', 'integer', 'min:1'],
            'staff_limit' => ['required', 'integer', 'min:1'],
        ]);

        Plan::create($validated);

        return back()->with('status', 'Subscription plan created successfully.');
    }
}
