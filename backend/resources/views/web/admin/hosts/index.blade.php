@php use App\Support\Fmt; @endphp
<x-layouts.panel space="admin" title="Gérants" heading="Gérants">
    <p class="mb-4 max-w-3xl text-sm text-gray-600 dark:text-gray-400">Un compte gérant = un établissement. Pour ajouter un gérant, créez un compte avec le rôle « Gérant » puis rattachez-lui un établissement.</p>
    <form method="GET" class="mb-4 flex max-w-xl gap-2">
        <input name="search" value="{{ $search }}" class="input" placeholder="Nom ou e-mail" aria-label="Rechercher un gérant">
        <button class="btn-primary" type="submit">Rechercher</button>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr><th class="px-4 py-3">Gérant</th><th class="px-4 py-3">Établissement</th><th class="px-4 py-3">Dossier</th><th class="px-4 py-3">Dernière connexion</th></tr></thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($hosts as $h)
                    <tr>
                        <td class="px-4 py-3"><a class="link" href="{{ route('admin.hosts.show', $h['id']) }}">{{ $h['name'] }}</a><span class="block text-xs text-gray-500">{{ $h['email'] }}@if ($h['phone']) · {{ $h['phone'] }}@endif</span></td>
                        <td class="px-4 py-3">{{ collect($h['accommodations'] ?? [])->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td class="px-4 py-3">@include('web.admin.hosts._compliance', ['status' => $h['compliance_status'] ?? null])</td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $h['last_login_at'] ? Fmt::datetime($h['last_login_at']) : 'Jamais' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Aucun gérant.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :meta="$meta" />
</x-layouts.panel>
