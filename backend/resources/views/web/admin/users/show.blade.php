@php use App\Support\Fmt; use App\Http\Controllers\Web\Admin\UserController as U; $blocked = $u['blocked_at'] || $u['status'] === 'blocked'; @endphp
<x-layouts.panel space="admin" :title="$u['name']">
    <a href="{{ route('admin.users') }}" class="link mb-3 inline-flex items-center gap-1 text-sm"><x-icon name="chevron-left" /> Clients et comptes</a>
    <div class="mb-5 flex flex-wrap items-center gap-3"><h1 class="page-title">{{ $u['name'] }}</h1>@if ($blocked)<span class="badge bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Bloqué</span>@endif</div>
    <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
        <div class="space-y-6">
            <form method="POST" action="{{ route('admin.users.update', $u['id']) }}" class="card grid gap-4 sm:grid-cols-2">
                @csrf @method('PUT')
                <x-input name="name" label="Nom complet" :value="$u['name']" required />
                <x-input name="email" type="email" label="E-mail" :value="$u['email']" required />
                <x-input name="phone" label="Téléphone" :value="$u['phone']" />
                <x-select name="role" label="Rôle" :options="U::ROLES" :value="$u['role']" />
                <div class="sm:col-span-2"><button class="btn-primary" type="submit">Enregistrer</button></div>
            </form>
            <section class="card">
                <h2 class="section-title">Réservations</h2>
                <ul class="mt-3 divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    @forelse (array_slice($u['bookings'] ?? [], 0, 10) as $b)
                        <li class="flex items-center justify-between gap-3 py-2"><a class="link" href="{{ route('admin.reservations.show', $b['id']) }}">{{ $b['booking_number'] ?? '#' . $b['id'] }}</a><span>{{ Fmt::date($b['check_in']) }} → {{ Fmt::date($b['check_out']) }}</span><x-status :status="$b['status']" /></li>
                    @empty
                        <li class="py-2 text-gray-500">Aucune réservation.</li>
                    @endforelse
                </ul>
            </section>
        </div>
        <aside class="space-y-4">
            <section class="card space-y-2 text-sm">
                <p><span class="text-gray-500">Inscrit le</span> {{ Fmt::date($u['created_at']) }}</p>
                <p><span class="text-gray-500">Dernière connexion</span> {{ $u['last_login_at'] ? Fmt::datetime($u['last_login_at']) : 'Jamais' }}</p>
                <p><span class="text-gray-500">Points fidélité</span> {{ $u['loyalty_points_balance'] ?? 0 }}</p>
                <p><span class="text-gray-500">Double authentification</span> {{ $u['two_factor_enabled'] ? 'Activée' : 'Non' }}</p>
            </section>
            <section class="card space-y-3">
                <form method="POST" action="{{ route('admin.users.act', [$u['id'], 'reset-password']) }}">@csrf<button class="btn-secondary w-full" type="submit">Envoyer un lien de mot de passe</button></form>
                @if ($blocked)
                    <form method="POST" action="{{ route('admin.users.act', [$u['id'], 'unblock']) }}">@csrf<button class="btn-secondary w-full" type="submit">Débloquer le compte</button></form>
                @elseif ($u['id'] !== auth()->id())
                    <x-modal-confirm :action="route('admin.users.act', [$u['id'], 'block'])" title="Bloquer ce compte ?" confirm="Bloquer" button="Bloquer le compte" button-class="btn-secondary w-full" danger>
                        La personne ne pourra plus se connecter.
                        <textarea name="reason" rows="2" class="input mt-3" placeholder="Motif (facultatif)" aria-label="Motif du blocage" maxlength="1000"></textarea>
                    </x-modal-confirm>
                @endif
                @if ($u['id'] !== auth()->id())
                    <x-modal-confirm :action="route('admin.users.destroy', $u['id'])" method="DELETE" :title="'Supprimer le compte de ' . $u['name'] . ' ?'" confirm="Supprimer définitivement" button="Supprimer le compte" button-class="btn-ghost w-full text-red-700" danger>
                        Cette action est définitive. Les réservations passées restent dans l’historique des établissements.
                    </x-modal-confirm>
                @endif
            </section>
        </aside>
    </div>
</x-layouts.panel>
