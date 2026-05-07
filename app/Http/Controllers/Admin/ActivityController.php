<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Store;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::query()
            ->with('causer', 'subject', 'store:id,name')
            ->orderByDesc('created_at');

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('causer_id')) {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(50);

        $eventTypes = ActivityLog::query()
            ->distinct()
            ->pluck('event_type')
            ->sort()
            ->values();

        $stores = Store::orderBy('name')
            ->pluck('name', 'id');

        return view('admin.activity.index', compact('logs', 'eventTypes', 'stores'));
    }
}
