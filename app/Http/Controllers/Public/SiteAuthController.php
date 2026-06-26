<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\SiteUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use RuntimeException;

class SiteAuthController extends Controller
{
    public function showLogin(Request $request): RedirectResponse
    {
        return redirect()->route('home', ['lang' => $request->query('lang', 'az')])
            ->with('auth_flash_modal', 'login');
    }

    public function showRegister(Request $request): RedirectResponse
    {
        return redirect()->route('home', ['lang' => $request->query('lang', 'az')])
            ->with('auth_flash_modal', 'register');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = SiteUser::query()->active()->where('email', mb_strtolower($credentials['email']))->first();

        if (!$user || !$user->password_hash || !Hash::check($credentials['password'], $user->password_hash)) {
            return back()->with('auth_flash_error', 'Email və ya şifrə yanlışdır.')->with('auth_flash_modal', 'login');
        }

        $request->session()->regenerate();
        $request->session()->put('site_user_id', $user->id);
        $user->forceFill(['last_login_at' => now()])->save();

        return back();
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:aa_site_users,email'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = SiteUser::query()->create([
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? null,
            'email' => mb_strtolower($data['email']),
            'password_hash' => Hash::make($data['password']),
            'email_notify' => true,
            'is_active' => true,
        ]);

        $request->session()->regenerate();
        $request->session()->put('site_user_id', $user->id);

        return back();
    }

    public function googleLogin(Request $request): RedirectResponse
    {
        if (! $this->googleEnabled()) {
            return $this->googleFail($request);
        }

        $state = Str::random(64);
        $lang = (string) $request->query('lang', $request->session()->get('site_lang', 'az'));

        $request->session()->put('google_oauth_state', $state);
        $request->session()->put('google_oauth_lang', $lang);

        $params = [
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'prompt' => 'select_account',
            'access_type' => 'online',
        ];

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params));
    }

    public function googleCallback(Request $request): RedirectResponse
    {
        try {
            if (! $this->googleEnabled() || $request->filled('error')) {
                return $this->googleFail($request);
            }

            $state = (string) $request->query('state', '');
            $savedState = (string) $request->session()->pull('google_oauth_state', '');

            if ($state === '' || $savedState === '' || ! hash_equals($savedState, $state)) {
                return $this->googleFail($request);
            }

            $code = (string) $request->query('code', '');
            if ($code === '') {
                return $this->googleFail($request);
            }

            $token = Http::asForm()->timeout(25)->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code',
            ])->throw()->json();

            $accessToken = (string) ($token['access_token'] ?? '');
            if ($accessToken === '') {
                throw new RuntimeException('Google access token alınmadı.');
            }

            $profile = Http::withToken($accessToken)
                ->timeout(25)
                ->acceptJson()
                ->get('https://openidconnect.googleapis.com/v1/userinfo')
                ->throw()
                ->json();

            $user = $this->loginGoogleProfile($profile);
            $request->session()->regenerate();
            $request->session()->put('site_user_id', $user->id);

            $lang = (string) $request->session()->pull('google_oauth_lang', 'az');
            return redirect()->route('profile', ['lang' => $lang]);
        } catch (\Throwable $exception) {
            return $this->googleFail($request, config('app.debug') ? $exception->getMessage() : null);
        }
    }

    public function googleTokenLogin(Request $request): RedirectResponse
    {
        try {
            if (! $this->googleEnabled()) {
                return $this->googleFail($request);
            }

            $request->validate(['access_token' => ['required', 'string']]);

            $profile = Http::withToken((string) $request->input('access_token'))
                ->timeout(25)
                ->acceptJson()
                ->get('https://www.googleapis.com/oauth2/v3/userinfo')
                ->throw()
                ->json();

            $user = $this->loginGoogleProfile($profile);
            $request->session()->regenerate();
            $request->session()->put('site_user_id', $user->id);

            return redirect()->route('profile', ['lang' => (string) $request->input('lang', 'az')]);
        } catch (\Throwable $exception) {
            return $this->googleFail($request, config('app.debug') ? $exception->getMessage() : null);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    private function loginGoogleProfile(array $profile): SiteUser
    {
        $googleId = trim((string) ($profile['sub'] ?? ''));
        $email = mb_strtolower(trim((string) ($profile['email'] ?? '')), 'UTF-8');
        $fullName = trim((string) ($profile['name'] ?? '')) ?: $email;
        $avatar = trim((string) ($profile['picture'] ?? ''));

        if ($googleId === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Google profil məlumatı düzgün deyil.');
        }

        $user = SiteUser::query()
            ->where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $googleId,
                'avatar_url' => $avatar,
                'full_name' => trim((string) $user->full_name) !== '' ? $user->full_name : $fullName,
                'is_active' => true,
                'last_login_at' => now(),
            ])->save();

            return $user;
        }

        return SiteUser::query()->create([
            'full_name' => $fullName,
            'phone' => null,
            'email' => $email,
            'password_hash' => null,
            'google_id' => $googleId,
            'avatar_url' => $avatar,
            'email_notify' => true,
            'is_active' => true,
            'last_login_at' => now(),
        ]);
    }

    private function googleEnabled(): bool
    {
        return (bool) config('services.google.enabled')
            && trim((string) config('services.google.client_id')) !== ''
            && trim((string) config('services.google.client_secret')) !== ''
            && trim((string) config('services.google.redirect')) !== '';
    }

    private function googleFail(Request $request, ?string $debugMessage = null): RedirectResponse
    {
        $message = 'Google ilə giriş zamanı xəta baş verdi.';
        if ($debugMessage) {
            $message .= ' ' . $debugMessage;
        }

        $lang = (string) $request->session()->get('google_oauth_lang', $request->query('lang', 'az'));

        return redirect()->route('home', ['lang' => $lang])
            ->with('auth_flash_error', $message)
            ->with('auth_flash_modal', 'login');
    }
}
