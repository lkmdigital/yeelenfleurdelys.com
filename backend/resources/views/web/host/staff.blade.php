@php use App\Support\Fmt; @endphp
<x-layouts.panel space="host" title="Utilisateurs" heading="Utilisateurs de l’établissement">
    @if ($denied)
        <x-empty title="Accès réservé" icon="user-cog">{{ $deniedMessage }}</x-empty>
    @else
        <p class="mb-4 max-w-3xl text-sm text-gray-600 dark:text-gray-400">Donnez un accès à vos collaborateurs (réception, comptabilité…). Chacun reçoit une invitation par e-mail pour créer son mot de passe et ne voit que les menus cochés.</p>
        <div class="grid gap-6 xl:grid-cols-[1fr_380px]">
            <section class="space-y-4">
                @forelse ($staff as $m)
                    <article class="card" x-data="{ edit: false }">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="font-semibold">{{ $m['name'] }} <span class="ml-1 text-sm font-normal text-gray-500">{{ $roles[$m['role']] ?? $m['role'] }}</span></p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $m['email'] }}@if ($m['phone']) · {{ $m['phone'] }}@endif</p>
                                <p class="mt-1 text-xs text-gray-500">{{ collect($m['permissions'] ?? [])->map(fn ($p) => $permissions[$p] ?? null)->filter()->implode(', ') ?: 'Tableau de bord seulement' }}</p>
                            </div>
                            <span class="badge {{ ['active' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300', 'invited' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'][$m['status']] ?? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300' }}">{{ ['active' => 'Actif', 'invited' => 'Invitation envoyée', 'suspended' => 'Suspendu'][$m['status']] ?? $m['status'] }}</span>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <button type="button" class="btn-secondary btn-sm" @click="edit = !edit"><x-icon name="pencil" /> Modifier les accès</button>
                            @if ($m['status'] !== 'invited')
                                <form method="POST" action="{{ route('host.staff.update', $m['id']) }}">@csrf @method('PUT')<input type="hidden" name="status" value="{{ $m['status'] === 'suspended' ? 'active' : 'suspended' }}"><button class="btn-ghost btn-sm" type="submit">{{ $m['status'] === 'suspended' ? 'Réactiver' : 'Suspendre' }}</button></form>
                            @endif
                            <x-modal-confirm :action="route('host.staff.destroy', $m['id'])" method="DELETE" :title="'Retirer l’accès de ' . $m['name'] . ' ?'" confirm="Retirer l’accès" button="Retirer" button-class="btn-ghost btn-sm text-red-700" danger>Cette personne ne pourra plus se connecter à l’espace de gestion.</x-modal-confirm>
                        </div>
                        <form x-show="edit" x-cloak method="POST" action="{{ route('host.staff.update', $m['id']) }}" class="mt-4 space-y-3 border-t border-gray-200 pt-4 dark:border-gray-800">
                            @csrf @method('PUT')
                            <x-select name="role" :id="'role_' . $m['id']" label="Poste" :options="$roles" :value="$m['role']" />
                            <fieldset><legend class="label">Menus accessibles</legend>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @foreach ($permissions as $k => $l)
                                        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="permissions[]" value="{{ $k }}" @checked(in_array($k, $m['permissions'] ?? [], true)) class="h-4 w-4 rounded border-gray-300 accent-[rgb(var(--brand))]"> {{ $l }}</label>
                                    @endforeach
                                </div>
                            </fieldset>
                            <button class="btn-primary btn-sm" type="submit">Enregistrer</button>
                        </form>
                    </article>
                @empty
                    <x-empty title="Aucun collaborateur" icon="user-cog" />
                @endforelse
            </section>
            <form method="POST" action="{{ route('host.staff.store') }}" class="card space-y-3 self-start" x-data="{ role: @js(old('role', 'receptionniste')), defaults: @js($defaults), perms: @js(old('permissions', $defaults['receptionniste'] ?? [])) }">
                @csrf
                <h2 class="section-title">Inviter un collaborateur</h2>
                <x-input name="name" label="Nom complet" required />
                <x-input name="email" type="email" label="E-mail" required />
                <x-input name="phone" label="Téléphone" />
                <x-select name="role" label="Poste" :options="$roles" value="receptionniste" x-model="role" x-on:change="perms = defaults[role] || []" />
                <fieldset><legend class="label">Menus accessibles</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @foreach ($permissions as $k => $l)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="permissions[]" value="{{ $k }}" x-model="perms" class="h-4 w-4 rounded border-gray-300 accent-[rgb(var(--brand))]"> {{ $l }}</label>
                        @endforeach
                    </div>
                </fieldset>
                <button class="btn-primary w-full" type="submit">Envoyer l’invitation</button>
            </form>
        </div>
    @endif
</x-layouts.panel>
