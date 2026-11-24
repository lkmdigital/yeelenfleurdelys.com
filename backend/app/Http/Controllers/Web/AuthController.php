<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TwoFactorService;
use App\Support\ApiResult;
use App\Support\InternalApi;
use App\Support\Nav;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Connexion, inscription et récupération de compte du site Blade.
 * La vérification du mot de passe, le verrouillage après 5 échecs, l'OTP e-mail et
 * l'activation passent par les endpoints de l'API (AuthController), déjà testés.
 */
class AuthController extends Controller
{
    /** Messages de l'API encore en anglais. */
    private const MESSAGES = [
        'The provided credentials are incorrect.' => 'E-mail ou mot de passe incorrect.',
        'Your account has been blocked. Please contact support.' => 'Ce compte est bloqué. Contactez-nous pour le réactiver.',
    ];

    public function showLogin(Request $request)
    {
        if ($request->filled('redirect')) {
            $this->rememberRedirect($request->query('redirect'));
        }
        return view('web.auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string|max:255',
        ], [
            'email.required' => "L'adresse e-mail est requise.",
            'email.email' => "L'adresse e-mail n'est pas valide.",
            'password.required' => 'Le mot de passe est requis.',
        ]);

        $res = InternalApi::post('login', $data + ['remember' => $request->boolean('remember')]);

        if (!$res->ok()) {
            return back()->withErrors($this->translate($res))->onlyInput('email', 'remember');
        }

        if ($res->json('requires_2fa')) {
            // Le jeton temporaire de l'API ne sert pas au site : la vérification se fait ici.
            $user = User::find($res->json('user_id'));
            $user?->tokens()->where('name', '2fa-verification')->delete();
            $request->session()->put('auth.2fa', ['user_id' => $user?->id, 'remember' => $request->boolean('remember'), 'at' => now()->timestamp]);
            return redirect()->route('2fa');
        }

        if ($res->json('requires_email_otp')) {
            $request->session()->put('auth.otp', ['user_id' => $res->json('user_id'), 'email' => $data['email']]);
            return redirect()->route('otp')->with('status', $res->message());
        }

        return $this->loggedIn($request);
    }

    public function show2fa(Request $request)
    {
        if (!$request->session()->has('auth.2fa')) {
            return redirect()->route('login');
        }
        return view('web.auth.two-factor');
    }

    public function verify2fa(Request $request, TwoFactorService $twoFactor)
    {
        $pending = $request->session()->get('auth.2fa');
        if (!$pending || now()->timestamp - ($pending['at'] ?? 0) > 600) {
            $request->session()->forget('auth.2fa');
            return redirect()->route('login')->withErrors(['email' => 'La vérification a expiré. Connectez-vous à nouveau.']);
        }

        $request->validate(['code' => 'required|string|max:12'], ['code.required' => 'Saisissez le code.']);
        $user = User::find($pending['user_id']);
        $code = preg_replace('/\s+/', '', (string) $request->input('code'));

        $ok = $user && $user->two_factor_enabled && (strlen($code) === 6
            ? $twoFactor->verifyCode($user, $code)
            : $twoFactor->verifyRecoveryCode($user, strtoupper($code)));

        if (!$ok) {
            Log::channel('security')->warning('Web 2FA failed', ['user_id' => $user?->id, 'ip' => $request->ip()]);
            return back()->withErrors(['code' => 'Le code de vérification est incorrect.']);
        }

        $request->session()->forget('auth.2fa');
        $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip(), 'login_count' => ($user->login_count ?? 0) + 1]);
        Auth::guard('web')->login($user, (bool) ($pending['remember'] ?? false));
        $request->session()->regenerate();

        return $this->loggedIn($request);
    }

    public function showOtp(Request $request)
    {
        if (!$request->session()->has('auth.otp')) {
            return redirect()->route('login');
        }
        return view('web.auth.verify-otp', ['email' => $request->session()->get('auth.otp.email')]);
    }

    public function verifyOtp(Request $request)
    {
        $pending = $request->session()->get('auth.otp');
        if (!$pending) {
            return redirect()->route('login');
        }

        if ($request->boolean('resend')) {
            $res = InternalApi::post('auth/send-otp', ['email' => $pending['email']]);
            return back()->with($res->ok() ? 'status' : 'error', $res->ok() ? 'Un nouveau code vient de vous être envoyé.' : $res->message());
        }

        $request->validate(['code' => 'required|digits:6'], [
            'code.required' => 'Saisissez le code reçu.',
            'code.digits' => 'Le code comporte 6 chiffres.',
        ]);

        $res = InternalApi::post('auth/verify-otp', ['user_id' => $pending['user_id'], 'code' => $request->input('code')]);
        if (!$res->ok()) {
            return back()->withErrors($this->translate($res));
        }

        $request->session()->forget('auth.otp');
        return $this->loggedIn($request);
    }

    public function showRegister(Request $request)
    {
        return view('web.auth.register', ['email' => $request->query('email')]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'accept_terms' => 'accepted',
        ], [
            'accept_terms.accepted' => 'Vous devez accepter les conditions générales.',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $res = InternalApi::post('auth/register-traveler', $request->only(
            'first_name', 'last_name', 'email', 'phone', 'password', 'password_confirmation', 'nationality'
        ) + ['accept_terms' => 1, 'traveler_type' => 'individual']);

        if (!$res->ok()) {
            return back()->withErrors($this->translate($res))->withInput($request->except('password', 'password_confirmation'));
        }

        if ($res->json('requires_email_otp')) {
            $request->session()->put('auth.otp', ['user_id' => $res->json('user_id'), 'email' => $request->input('email')]);
            return redirect()->route('otp')->with('status', $res->message());
        }

        return $this->loggedIn($request);
    }

    public function showForgot()
    {
        return view('web.auth.forgot-password');
    }

    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email'], ['email.required' => "L'adresse e-mail est requise."]);
        $res = InternalApi::post('auth/forgot-password', ['email' => $request->input('email')]);
        if (!$res->ok() && $res->status() !== 200) {
            return back()->withErrors($this->translate($res))->withInput();
        }
        return back()->with('status', 'Si un compte existe avec cette adresse, un lien de réinitialisation vient de lui être envoyé.');
    }

    public function showReset(Request $request)
    {
        return view('web.auth.reset-password', ['token' => $request->query('token'), 'email' => $request->query('email')]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], ['password.confirmed' => 'Les deux mots de passe ne correspondent pas.']);

        $res = InternalApi::post('auth/reset-password', $request->only('token', 'email', 'password', 'password_confirmation'));
        if (!$res->ok()) {
            return back()->withErrors($this->translate($res))->withInput($request->only('email', 'token'));
        }
        return redirect()->route('login')->with('status', 'Mot de passe modifié. Vous pouvez vous connecter.');
    }

    /** Client ayant réservé sans compte : il choisit son mot de passe. */
    public function showActivate(Request $request)
    {
        $prefill = $request->filled('email')
            ? InternalApi::get('auth/guest-prefill', ['email' => $request->query('email')])->json()
            : [];
        return view('web.auth.activate', ['email' => $request->query('email'), 'prefill' => $prefill]);
    }

    public function activate(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ], ['password.confirmed' => 'Les deux mots de passe ne correspondent pas.']);

        $res = InternalApi::post('auth/activate-guest', $request->only('email', 'password', 'password_confirmation', 'name', 'phone'));
        if (!$res->ok()) {
            return back()->withErrors($this->translate($res))->withInput($request->except('password', 'password_confirmation'));
        }
        if ($res->json('requires_email_otp')) {
            $request->session()->put('auth.otp', ['user_id' => $res->json('user_id'), 'email' => $request->input('email')]);
            return redirect()->route('otp')->with('status', $res->message());
        }
        return $this->loggedIn($request);
    }

    /** Collaborateur invité par un gérant. */
    public function showActivateStaff(Request $request)
    {
        $token = (string) $request->query('token');
        $info = $token ? InternalApi::get('auth/staff-invitation', ['token' => $token]) : null;
        return view('web.auth.activate-staff', [
            'token' => $token,
            'info' => $info?->ok() ? $info->json() : null,
            'invalid' => !$info || !$info->ok(),
        ]);
    }

    public function activateStaff(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], ['password.confirmed' => 'Les deux mots de passe ne correspondent pas.']);

        $res = InternalApi::post('auth/activate-staff', $request->only('token', 'password', 'password_confirmation'));
        if (!$res->ok()) {
            return back()->withErrors($this->translate($res));
        }
        return $this->loggedIn($request, 'Votre accès est activé. Bienvenue !');
    }

    /** Retour de Google : la session est déjà ouverte par l'API (sinon, échec). */
    public function oauthCallback()
    {
        return redirect()->route('login')->withErrors(['email' => "La connexion avec Google n'a pas abouti. Réessayez ou utilisez votre e-mail."]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user?->tokens()->where('name', 'auth_token')->delete();
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'Vous êtes déconnecté.');
    }

    // ────────────────────────────────────────────────────────────────────

    private function loggedIn(Request $request, ?string $message = null)
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'La connexion a échoué. Réessayez.']);
        }

        // L'API crée un jeton Bearer à chaque connexion ; le site n'utilise que la session.
        $user->tokens()->where('name', 'auth_token')->where('created_at', '>=', now()->subMinutes(5))->delete();

        $intended = $request->session()->pull('url.intended');
        $target = $intended && $this->isSafe($intended) ? $intended : Nav::home($user);

        return redirect($target)->with('success', $message ?? 'Bienvenue, ' . ($user->first_name ?: $user->name) . ' !');
    }

    private function rememberRedirect(string $path): void
    {
        if ($this->isSafe($path)) {
            session()->put('url.intended', $path);
        }
    }

    /** Uniquement une adresse interne (pas de redirection ouverte). */
    private function isSafe(string $url): bool
    {
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            return true;
        }
        $host = parse_url($url, PHP_URL_HOST);
        return $host && $host === request()->getHost();
    }

    private function translate(ApiResult $res): array
    {
        $errors = $res->errors();
        if ($res->status() === 429 && !$res->json('errors')) {
            $errors = ['email' => [$res->message('Trop de tentatives. Réessayez dans quelques minutes.')]];
        }
        array_walk_recursive($errors, function (&$m) {
            $m = self::MESSAGES[$m] ?? $m;
        });
        return $errors;
    }
}
