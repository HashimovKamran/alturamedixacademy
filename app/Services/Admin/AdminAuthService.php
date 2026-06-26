<?php

namespace App\Services\Admin;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthService
{
    public function user(Request $request): ?AdminUser
    {
        $id = (int) $request->session()->get('admin_user_id', 0);
        return $id > 0 ? AdminUser::query()->active()->find($id) : null;
    }

    public function attempt(Request $request, string $username, string $password): bool
    {
        $admin = AdminUser::query()->active()->where('username', $username)->first();

        if (!$admin || !Hash::check($password, $admin->password_hash)) {
            return false;
        }

        $request->session()->regenerate();
        $request->session()->put('admin_user_id', $admin->id);
        $admin->forceFill(['last_login_at' => now()])->save();

        return true;
    }

    public function logout(Request $request): void
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
