<?php

namespace App\Http\Controllers\Web\Host;

use App\Support\InternalApi;
use Illuminate\Http\Request;

/** Paramètres du compte gérant : coordonnées, banque, mot de passe, notifications, double authentification. */
class SettingsController extends HostController
{
    public function index()
    {
        $this->property();
        $profile = InternalApi::get('host/profile');
        $me = InternalApi::get('me')->json();

        return $this->page('settings', [
            'profile' => $profile->ok() ? ($profile->json('user') ?? []) : [],
            'isOwner' => !auth()->user()->isStaff(),
            'me' => $me['user'] ?? $me,
            'twoFactor' => InternalApi::get('two-factor/status')->json(),
            'setup' => session('2fa_setup'),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'whatsapp' => ['required', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]{6,20}$/'],
            'address_line1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'rccm' => 'nullable|string|max:255',
            'tax_account_number' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_holder' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
        ], ['whatsapp.regex' => 'Le numéro WhatsApp ne doit contenir que des chiffres.']);

        $res = InternalApi::post('host/profile', array_filter($data, fn ($v) => $v !== null));
        return $res->failBack() ?? back()->with('success', 'Coordonnées enregistrées.');
    }

    public function password(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], ['password.confirmed' => 'Les deux mots de passe ne correspondent pas.', 'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.']);
        $res = InternalApi::post('me/password', $request->only('current_password', 'password', 'password_confirmation'));
        return $res->failBack() ?? back()->with('success', 'Mot de passe modifié.');
    }

    public function notifications(Request $request)
    {
        $res = InternalApi::put('me/profile', [
            'notif_email' => $request->boolean('notif_email'),
            'notif_whatsapp' => $request->boolean('notif_whatsapp'),
            'notif_sms' => $request->boolean('notif_sms'),
        ]);
        return $res->failBack() ?? back()->with('success', 'Préférences de notification enregistrées.');
    }

    public function twoFactor(Request $request, string $action)
    {
        if ($action === 'setup') {
            $res = InternalApi::post('two-factor/setup');
            if ($fail = $res->failBack()) {
                return $fail;
            }
            return back()->with('2fa_setup', ['secret' => $res->json('secret'), 'url' => $res->json('qr_code_url')]);
        }

        if ($action === 'enable') {
            $request->validate(['secret' => 'required|string', 'code' => 'required|digits:6'], ['code.required' => 'Saisissez le code à 6 chiffres de votre application.', 'code.digits' => 'Le code contient 6 chiffres.']);
            $res = InternalApi::post('two-factor/enable', $request->only('secret', 'code'));
            if (!$res->ok()) {
                return back()->with('2fa_setup', ['secret' => $request->input('secret'), 'url' => $request->input('url')])
                    ->withErrors(['code' => 'Code incorrect. Vérifiez l’heure de votre téléphone et réessayez.']);
            }
            $codes = $res->json('recovery_codes') ?? [];
            return back()->with('success', 'Double authentification activée.')->with('recovery_codes', $codes);
        }

        $request->validate(['password' => 'required|string'], ['password.required' => 'Saisissez votre mot de passe pour confirmer.']);
        $res = InternalApi::post('two-factor/disable', $request->only('password'));
        if (!$res->ok()) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }
        return back()->with('success', 'Double authentification désactivée.');
    }
}
