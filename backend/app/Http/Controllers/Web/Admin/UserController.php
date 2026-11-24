<?php

namespace App\Http\Controllers\Web\Admin;

use App\Support\InternalApi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Gérants et comptes (clients, gérants, administrateurs). */
class UserController extends AdminController
{
    public const ROLES = ['user' => 'Client', 'host' => 'Gérant', 'admin' => 'Administrateur'];

    public function hosts(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $res = InternalApi::get('admin/hosts', $this->only(['search' => $search, 'page' => $request->integer('page') ?: null, 'per_page' => 20]));
        return view('web.admin.hosts.index', ['hosts' => $res->json('data', []), 'meta' => $this->meta($res), 'search' => $search]);
    }

    public function host(int $id)
    {
        $res = InternalApi::get("admin/hosts/{$id}");
        abort_unless($res->ok(), 404);
        return view('web.admin.hosts.show', ['h' => $res->json('data')]);
    }

    public function hostAct(Request $request, int $id, string $action)
    {
        $request->validate(['comment' => ($action === 'reject' ? 'required' : 'nullable') . '|string|max:2000'], ['comment.required' => 'Indiquez le motif du refus.']);
        $res = InternalApi::post("admin/hosts/{$id}/{$action}", array_filter(['comment' => $request->input('comment')]));
        $done = ['validate' => 'Dossier du gérant validé.', 'reject' => 'Dossier refusé. Le gérant est prévenu.', 'suspend' => 'Gérant suspendu.'][$action];
        return $res->failBack() ?? back()->with('success', $done);
    }

    public function index(Request $request)
    {
        $filters = $this->only([
            'search' => trim((string) $request->query('search')),
            'role' => array_key_exists((string) $request->query('role'), self::ROLES) ? $request->query('role') : null,
            'status' => in_array($request->query('status'), ['active', 'blocked'], true) ? $request->query('status') : null,
        ]);
        $res = InternalApi::get('admin/users', $filters + ['page' => $request->integer('page') ?: 1, 'per_page' => 20]);
        return view('web.admin.users.index', ['users' => $res->json('data', []), 'meta' => $this->meta($res), 'filters' => $filters]);
    }

    public function create()
    {
        return view('web.admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:' . implode(',', array_keys(self::ROLES)),
        ]);
        // Mot de passe aléatoire jamais communiqué : la personne choisit le sien via le lien envoyé par e-mail.
        $res = InternalApi::post('admin/users', $data + ['password' => Str::password(24), 'status' => 'active']);
        if ($fail = $res->failBack()) {
            return $fail;
        }
        $id = $res->json('data.id') ?? $res->json('user.id') ?? $res->json('id');
        $mail = $id ? InternalApi::post("admin/users/{$id}/reset-password") : null;
        $msg = $mail?->ok() ? 'Compte créé. Un e-mail a été envoyé pour choisir le mot de passe.' : 'Compte créé, mais l’e-mail de mot de passe n’est pas parti : utilisez « Envoyer un lien de mot de passe ».';
        return redirect()->route($id ? 'admin.users.show' : 'admin.users', $id ? [$id] : [])->with('success', $msg);
    }

    public function show(int $id)
    {
        $res = InternalApi::get("admin/users/{$id}");
        abort_unless($res->ok(), 404);
        return view('web.admin.users.show', ['u' => $res->json('data')]);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:' . implode(',', array_keys(self::ROLES)),
        ]);
        $res = InternalApi::put("admin/users/{$id}", $data);
        return $res->failBack() ?? back()->with('success', 'Compte enregistré.');
    }

    public function act(Request $request, int $id, string $action)
    {
        $request->validate(['reason' => 'nullable|string|max:1000']);
        $res = InternalApi::post("admin/users/{$id}/{$action}", array_filter(['reason' => $request->input('reason')]));
        $done = ['block' => 'Compte bloqué : la personne ne peut plus se connecter.', 'unblock' => 'Compte débloqué.', 'reset-password' => 'Lien de réinitialisation du mot de passe envoyé par e-mail.'][$action];
        return $res->failBack() ?? back()->with('success', $done);
    }

    public function destroy(int $id)
    {
        $res = InternalApi::delete("admin/users/{$id}");
        return $res->failBack() ?? redirect()->route('admin.users')->with('success', 'Compte supprimé.');
    }
}
