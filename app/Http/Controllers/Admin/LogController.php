<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AdminLog::query();

        if ($request->filled('module')) {
            $query->where('module', $request->query('module'));
        }

        if ($request->filled('action')) {
            $query->where('action', $request->query('action'));
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->query('q'));
            $query->where(function ($builder) use ($q): void {
                $builder->where('admin_name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('object_type', 'like', '%' . $q . '%')
                    ->orWhere('ip_address', 'like', '%' . $q . '%');
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->query('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->query('date_to'));
        }

        return view('admin.logs', [
            'logs' => $query->latest('created_at')->paginate(80)->withQueryString(),
            'modules' => AdminLog::query()->select('module')->distinct()->orderBy('module')->pluck('module'),
            'actions' => AdminLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
            'stats' => [
                'total' => AdminLog::query()->count(),
                'today' => AdminLog::query()->whereDate('created_at', now()->toDateString())->count(),
                'creates' => AdminLog::query()->where('action', 'create')->count(),
                'deletes' => AdminLog::query()->where('action', 'delete')->count(),
            ],
            'filters' => $request->only(['module', 'action', 'q', 'date_from', 'date_to']),
        ]);
    }
}
