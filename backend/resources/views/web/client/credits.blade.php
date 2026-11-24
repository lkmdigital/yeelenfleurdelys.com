@php use App\Support\Fmt; @endphp
<x-layouts.panel space="client" title="Mes avoirs" heading="Mes avoirs">
    <x-kpi class="mb-4 max-w-xs" label="Solde disponible" :value="Fmt::price($balance)" icon="wallet" />
    <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">Un avoir est crédité quand vous annulez une réservation modifiable. Il est déduit automatiquement de votre prochaine réservation.</p>
    @if (!$credits)
        <x-empty title="Aucun avoir" icon="wallet" />
    @else
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Date</th><th>Origine</th><th>Statut</th><th>Expire le</th><th class="text-right">Montant</th></tr></thead>
            <tbody>@foreach ($credits as $c)
                <tr><td>{{ Fmt::date($c['created_at']) }}</td><td>{{ $c['note'] ?? 'Annulation' }}</td><td>{{ ['available' => 'Disponible', 'used' => 'Utilisé', 'expired' => 'Expiré'][$c['status']] ?? $c['status'] }}</td><td>{{ Fmt::date($c['expires_at']) }}</td><td class="text-right font-medium">{{ Fmt::price($c['amount']) }}</td></tr>
            @endforeach</tbody>
        </table></div>
    @endif
</x-layouts.panel>
