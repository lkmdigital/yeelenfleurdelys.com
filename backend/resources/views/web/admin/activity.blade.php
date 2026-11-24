@php use App\Support\Fmt; @endphp
<x-layouts.panel space="admin" title="Journal d’activité" heading="Journal d’activité">
    <div class="table-wrap">
        <table class="table">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Personne</th><th class="px-4 py-3">Action</th><th class="px-4 py-3">Adresse IP</th></tr></thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($logs as $l)
                    <tr>
                        <td class="whitespace-nowrap px-4 py-3">{{ Fmt::datetime($l['created_at']) }}</td>
                        <td class="px-4 py-3">{{ $l['actor']['name'] ?? 'Système' }}<span class="block text-xs text-gray-500">{{ $l['actor']['email'] ?? '' }}</span></td>
                        <td class="px-4 py-3">{{ $l['description'] ?: $l['action'] }}@if ($l['target'] ?? null)<span class="block text-xs text-gray-500">{{ $l['target']['type'] }} #{{ $l['target']['id'] }}</span>@endif</td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $l['ip_address'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Aucune activité enregistrée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :meta="$meta" />
</x-layouts.panel>
