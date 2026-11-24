@php use App\Support\Fmt; use App\Http\Controllers\Web\Admin\AccommodationController as A; @endphp
<x-layouts.panel space="admin" title="Établissements">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 class="page-title">Établissements</h1>
        <a href="{{ route('admin.accommodations.create') }}" class="btn-primary"><x-icon name="plus" /> Ajouter un établissement</a>
    </div>
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-2">
        <x-input name="search" label="Rechercher" :value="$filters['search'] ?? ''" placeholder="Nom, ville, gérant…" class="min-w-[240px] flex-1" />
        <x-select name="status" label="Statut" :options="A::STATUSES" :value="$filters['status'] ?? 'all'" />
        <button class="btn-primary" type="submit">Filtrer</button>
    </form>
    <div class="table-wrap">
        <table class="table">
            <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr><th class="px-4 py-3">Établissement</th><th class="px-4 py-3">Gérant</th><th class="px-4 py-3 text-right">Chambres</th><th class="px-4 py-3 text-right">Encaissé (30 j)</th><th class="px-4 py-3">Statut</th></tr></thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($items as $a)
                    @php [$st, $cls] = Fmt::accommodationStatus($a['status']); @endphp
                    <tr>
                        <td class="px-4 py-3"><a class="link" href="{{ route('admin.accommodations.show', $a['id']) }}">{{ $a['name'] }}</a><span class="block text-xs text-gray-500">{{ $a['establishment_code'] }} · {{ A::TYPES[$a['type']] ?? $a['type'] }} · {{ $a['city'] }}</span></td>
                        <td class="px-4 py-3">{{ $a['host']['name'] ?? '—' }}<span class="block text-xs text-gray-500">{{ $a['host']['email'] ?? '' }}</span></td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ $a['stats']['total_rooms'] ?? 0 }}</td>
                        <td class="px-4 py-3 text-right tabular-nums">{{ Fmt::price($a['stats']['amount_collected_30d'] ?? 0) }}</td>
                        <td class="px-4 py-3"><span class="badge {{ $cls }}">{{ $st }}</span>@if ($a['is_featured'])<span class="badge ml-1 bg-secondary text-onaccent">Mis en avant</span>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Aucun établissement.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pager :meta="$meta" />
</x-layouts.panel>
