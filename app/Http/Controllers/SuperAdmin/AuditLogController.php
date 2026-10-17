<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\School;
use App\Support\AnyFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with(['actor', 'school']);

        $action = $request->filled('action') ? '%'.trim($request->action).'%' : null;
        $hasDateFilter = $request->filled('date_from') || $request->filled('date_to');

        AnyFilter::apply($query, [
            $request->filled('school_id') ? fn (Builder $filter) => $filter->where('school_id', $request->school_id) : null,
            $action ? fn (Builder $filter) => $filter->where('action', 'like', $action) : null,
            $hasDateFilter ? function (Builder $filter) use ($request): void {
                if ($request->filled('date_from')) {
                    $filter->whereDate('created_at', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $filter->whereDate('created_at', '<=', $request->date_to);
                }
            } : null,
        ]);

        $logs = $query->latest('created_at')->paginate(50)->withQueryString();
        $schools = School::orderBy('name')->get();

        return view('super-admin.audit-logs.index', compact('logs', 'schools'));
    }
}
