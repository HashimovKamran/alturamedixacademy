<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteUser;
use App\Services\Admin\AdminLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = SiteUser::query();

        if ($request->filled('q')) {
            $q = trim((string) $request->query('q'));
            $query->where(function ($builder) use ($q): void {
                $builder->where('full_name', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%')
                    ->orWhere('phone', 'like', '%' . $q . '%')
                    ->orWhere('google_id', 'like', '%' . $q . '%');
            });
        }

        if ($request->query('active') !== null && $request->query('active') !== '') {
            $query->where('is_active', $request->query('active') === '1');
        }

        if ($request->query('notify') !== null && $request->query('notify') !== '') {
            $query->where('email_notify', $request->query('notify') === '1');
        }

        return view('admin.users', [
            'users' => $query->latest()->paginate(50)->withQueryString(),
            'stats' => [
                'total' => SiteUser::query()->count(),
                'active' => SiteUser::query()->where('is_active', true)->count(),
                'blocked' => SiteUser::query()->where('is_active', false)->count(),
                'google' => SiteUser::query()->whereNotNull('google_id')->where('google_id', '<>', '')->count(),
            ],
            'filters' => $request->only(['q', 'active', 'notify']),
        ]);
    }

    public function toggle(Request $request, SiteUser $user, AdminLogService $logs): RedirectResponse
    {
        $field = $request->input('field') === 'email_notify' ? 'email_notify' : 'is_active';
        $user->forceFill([$field => ! $user->{$field}])->save();

        $logs->write($request, 'users', 'toggle', 'İstifadəçi yeniləndi: ' . $user->email . ' / ' . $field, 'SiteUser', (int) $user->id);

        return back()->with('status', 'İstifadəçi yeniləndi.');
    }

    public function destroy(Request $request, SiteUser $user, AdminLogService $logs): RedirectResponse
    {
        $email = $user->email;
        $id = (int) $user->id;
        $user->delete();

        $logs->write($request, 'users', 'delete', 'İstifadəçi silindi: ' . $email, 'SiteUser', $id);

        return back()->with('status', 'İstifadəçi silindi.');
    }
}
