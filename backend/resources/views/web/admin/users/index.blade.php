@php use App\Support\Fmt; use App\Http\Controllers\Web\Admin\UserController as U; @endphp
<x-layouts.panel space="admin" title="Clients et comptes">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 class="page-title">Clients et comptes</h1>
        <a href="{{ route('admin.users.create') }}" class="btn-primary"><x-icon name="plus" /> Créer un compte</a>
    </div>
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-2">
        <x-input name="search" label="Rechercher" :value="$filters['search'] ?? ''" placeholder="Nom, e-mail, téléphone" class="min-w-[240px] flex-1" />
        <x-select name="role" label="Rôle" :options="['all' => 'Tous les rôles'] + U::ROLES" :value="$filters['role'] ?? 'all'" />
        <x-select name="status" label="État" :options="['all' => 'Tous', 'active' => 'Actifs', 'blocked' => 'Bloqués']" :value="$filters['status'] ?? 'all'" />
        <button class="btn-primary" type="submit">Filtrer</button>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr><th class="px-4 py-3">Nom</th><th class="px-4 py-3">Rôle</th><th class="px-4 py-3">Inscrit le</th><th class="px-4 py-3">État</th></tr></thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($users as $u)
                    <tr>
                        <td class="px-4 py-3"><a class="link" href="{{ route('admin.users.show', $u['id']) }}">{{ $u['name'] }}</a><span class="block text-xs text-gray-500">{{ $u['email'] }}@if ($u['phone']) · {{ $u['phone'] }}@endif</span></td>
                        <td class="px-4 py-3">{{ U::ROLES[$u['role']] ?? $u['role'] }}</td>
                        <td class="px-4 py-3">{{ Fmt::date($u['created_at']) }}</td>
                        <td class="px-4 py-3">@if ($u['blocked_at'] || $u['status'] === 'blocked')<span class="badge bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">Bloqué</span>@else<span class="badge bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">Actif</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Aucun compte.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :meta="$meta" />
</x-layouts.panel>
