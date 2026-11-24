<x-layouts.panel space="admin" title="Programme fidélité" heading="Programme fidélité">
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-kpi label="Points distribués" :value="number_format($stats['points_awarded'] ?? 0, 0, ',', ' ')" icon="award" />
        <x-kpi label="Points utilisés" :value="number_format($stats['points_redeemed'] ?? 0, 0, ',', ' ')" icon="gift" />
        <x-kpi label="Bons émis / utilisés" :value="($stats['vouchers_issued'] ?? 0) . ' / ' . ($stats['vouchers_used'] ?? 0)" icon="ticket" />
        <x-kpi label="Établissements participants" :value="($stats['participating_establishments'] ?? 0) . ' / ' . ($stats['total_establishments'] ?? 0)" icon="building-2" />
    </div>
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="card">
            <h2 class="section-title">Niveaux des membres</h2>
            <ul class="mt-3 divide-y divide-gray-200 text-sm dark:divide-gray-800">
                @foreach ($tiers as $t)
                    <li class="flex justify-between py-2"><span class="font-medium">{{ $t['label'] }}</span><span class="text-gray-600 dark:text-gray-400">dès {{ number_format($t['min_points'], 0, ',', ' ') }} points · {{ $stats['members_by_tier'][$t['key']] ?? 0 }} membre(s)</span></li>
                @endforeach
            </ul>
        </section>
        <section class="card">
            <h2 class="section-title">Bons de réduction</h2>
            <ul class="mt-3 divide-y divide-gray-200 text-sm dark:divide-gray-800">
                @foreach ($rewards as $r)
                    <li class="flex justify-between py-2"><span>{{ number_format($r['points_required'], 0, ',', ' ') }} points</span><span class="font-semibold">-{{ (float) $r['discount_percent'] }} %</span></li>
                @endforeach
            </ul>
            <p class="mt-3 text-xs text-gray-500">Les points gagnés par FCFA dépensé et les bonus se règlent dans <a class="link" href="{{ route('admin.settings', 'fidelite') }}">Paramètres › Fidélité</a>.</p>
        </section>
    </div>
</x-layouts.panel>
