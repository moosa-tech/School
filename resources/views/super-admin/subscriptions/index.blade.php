@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Tenant Subscriptions</h1>
            <p class="text-slate-500 text-sm">Monitor expiration dates, manual renewals, and reconciled payment histories</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">School</th>
                        <th class="px-6 py-4">Plan</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Period</th>
                        <th class="px-6 py-4 text-right">Manual Payment Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-6 py-4 font-bold text-slate-900">
                                {{ $sub->school->name }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-indigo-700">{{ $sub->plan->name }}</span>
                                <div class="text-xs text-slate-400">${{ $sub->plan->price }}/{{ $sub->plan->billing_interval }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $sub->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($sub->status === 'trial' ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-700') }}">
                                    {{ ucfirst($sub->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-500">
                                {{ $sub->starts_at?->format('Y-m-d') }} to {{ $sub->ends_at?->format('Y-m-d') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('super-admin.subscriptions.payment', $sub) }}" method="POST" class="inline-flex items-center space-x-2">
                                    @csrf
                                    <input type="hidden" name="amount" value="{{ $sub->plan->price }}">
                                    <input type="hidden" name="provider" value="manual">
                                    <input type="hidden" name="provider_reference" value="MANUAL-RENEW-{{ strtoupper(uniqid()) }}">
                                    <button type="submit" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold px-3 py-1.5 rounded-lg transition">
                                        Renew (+1 Month)
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-slate-400">No active subscriptions.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $subscriptions->links() }}
        </div>
    </div>

</div>
@endsection
