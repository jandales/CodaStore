<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $group = $request->query('type');
        $prefix = ActivityLog::GROUPS[$group][1] ?? null;

        $entries = ActivityLog::with('admin')
            ->when($prefix, fn ($q) => $q->where('action', 'like', $prefix . '%'))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.activity.index', [
            'entries' => $entries,
            'group' => $prefix ? $group : null,
        ]);
    }
}
