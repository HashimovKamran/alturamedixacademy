<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(Request $request, AdminAuthService $auth): View|RedirectResponse
    {
        if ($auth->user($request)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function login(Request $request, AdminAuthService $auth): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (!$auth->attempt($request, $data['username'], $data['password'])) {
            return back()->withErrors(['username' => 'İstifadəçi adı və ya şifrə yanlışdır.'])->withInput(['username' => $data['username']]);
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request, AdminAuthService $auth): RedirectResponse
    {
        $auth->logout($request);
        return redirect()->route('admin.login');
    }
}