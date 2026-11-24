@php use App\Support\Fmt; $st = ['active' => ['En cours', 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'], 'scheduled' => ['À venir', 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'], 'expired' => ['Terminée', 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'], 'inactive' => ['Désactivée', 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300']]; @endphp
<x-layouts.panel space="admin" title="Promotions" heading="Promotions des établissements">
    <div class="table-wrap">
        <table class="table">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr><th class="px-4 py-3">Établissement</th><th class="px-4 py-3">Réduction</th><th class="px-4 py-3">Période</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3"></th></tr></thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($promotions as $p)
                    @php [$t, $c] = $st[$p['computed_status'] ?? ''] ?? [$p['computed_status'] ?? '—', 'bg-gray-100 text-gray-700']; @endphp
                    <tr>
                        <td class="px-4 py-3">{{ $p['accommodation']['name'] ?? '—' }}<span class="block text-xs text-gray-500">{{ $p['room']['name'] ?? 'Toutes les chambres' }}</span></td>
                        <td class="px-4 py-3 font-semibold">{{ ($p['discount_type'] ?? 'percent') === 'fixed' ? '-' . Fmt::price($p['discount_amount']) : '-' . (float) $p['discount_percent'] . ' %' }}@if ($p['promo_code'])<span class="block font-mono text-xs font-normal text-gray-500">{{ $p['promo_code'] }}</span>@endif</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ Fmt::date($p['start_date']) }} → {{ Fmt::date($p['end_date']) }}</td>
                        <td class="px-4 py-3"><span class="badge {{ $c }}">{{ $t }}</span></td>
                        <td class="px-4 py-3 text-right"><form method="POST" action="{{ route('admin.promotions.toggle', $p['id']) }}">@csrf<button class="btn-ghost btn-sm" type="submit">{{ $p['is_active'] ? 'Désactiver' : 'Activer' }}</button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Aucune promotion. Les gérants les créent depuis leur espace.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :meta="$meta" />
</x-layouts.panel>
