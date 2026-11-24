@php
    use App\Support\Fmt;
    $next = $loyalty['next_tier'] ?? null;
    $pct = $next && ($next['min_points'] ?? 0) > 0 ? min(100, round(100 * ($loyalty['points_lifetime'] ?? 0) / $next['min_points'])) : 100;
@endphp
<x-layouts.panel space="client" title="Programme fidélité" heading="Programme fidélité">
    <div class="grid gap-4 md:grid-cols-3">
        <div class="card md:col-span-2">
            <p class="text-sm text-gray-500">Votre niveau</p>
            <p class="font-title text-2xl font-extrabold text-primary">{{ $loyalty['tier']['label'] ?? '—' }}</p>
            <p class="mt-2 text-sm">{{ number_format($loyalty['points_balance'] ?? 0, 0, ',', ' ') }} points disponibles · {{ number_format($loyalty['points_lifetime'] ?? 0, 0, ',', ' ') }} cumulés</p>
            @if ($next)
                <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800"><div class="h-full bg-secondary" style="width: {{ $pct }}%"></div></div>
                <p class="mt-1 text-xs text-gray-500">Encore {{ $next['points_remaining'] }} points pour le niveau {{ $next['label'] }}.</p>
            @endif
        </div>
        <div class="card">
            <p class="text-sm text-gray-500">Votre code de parrainage</p>
            <p class="mt-1 select-all font-mono text-xl font-bold">{{ $loyalty['referral_code'] ?? '—' }}</p>
            <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">Vous et votre filleul recevez {{ $loyalty['referral_bonus_filleul'] ?? 0 }} points à sa première réservation.</p>
        </div>
    </div>

    <h2 class="section-title mb-3 mt-8">Échanger mes points</h2>
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($loyalty['reward_tiers'] ?? [] as $rt)
            <form method="POST" action="{{ route('client.programme.voucher') }}" class="card flex items-center justify-between gap-3">
                @csrf <input type="hidden" name="reward_tier_id" value="{{ $rt['id'] }}">
                <span><span class="block text-lg font-bold">− {{ rtrim(rtrim(number_format($rt['discount_percent'], 1, ',', ''), '0'), ',') }} %</span><span class="text-sm text-gray-500">{{ $rt['points_required'] }} points</span></span>
                <button type="submit" class="btn-secondary btn-sm" @disabled(!$rt['claimable'])>Obtenir</button>
            </form>
        @empty
            <p class="text-sm text-gray-500">Aucune récompense disponible pour le moment.</p>
        @endforelse
    </div>

    @if (!empty($loyalty['vouchers']))
        <h2 class="section-title mb-3 mt-8">Mes bons de réduction</h2>
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Code</th><th>Réduction</th><th>Statut</th><th>Expire le</th></tr></thead>
            <tbody>@foreach ($loyalty['vouchers'] as $v)
                <tr><td class="font-mono">{{ $v['code'] }}</td><td>{{ $v['discount_percent'] }} %</td><td>{{ ['available' => 'Disponible', 'used' => 'Utilisé', 'expired' => 'Expiré'][$v['status']] ?? $v['status'] }}</td><td>{{ Fmt::date($v['expires_at']) }}</td></tr>
            @endforeach</tbody>
        </table></div>
    @endif

    <h2 class="section-title mb-3 mt-8">Historique des points</h2>
    @if (!$history)
        <p class="text-sm text-gray-500">Vos points apparaîtront ici après votre premier séjour.</p>
    @else
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Date</th><th>Opération</th><th class="text-right">Points</th></tr></thead>
            <tbody>@foreach ($history as $h)
                <tr><td>{{ Fmt::date($h['created_at']) }}</td><td>{{ $h['label'] }}@if ($h['description']) <span class="text-gray-500">— {{ $h['description'] }}</span>@endif</td><td class="text-right font-medium {{ $h['points'] < 0 ? 'text-red-700' : 'text-green-700' }}">{{ $h['points'] > 0 ? '+' : '' }}{{ $h['points'] }}</td></tr>
            @endforeach</tbody>
        </table></div>
    @endif
</x-layouts.panel>
