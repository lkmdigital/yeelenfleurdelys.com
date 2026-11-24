@php use App\Support\Fmt; $st = ['completed' => ['Payé', 'bg-green-100 text-green-800'], 'pending' => ['En attente', 'bg-yellow-100 text-yellow-800'], 'failed' => ['Échoué', 'bg-red-100 text-red-800'], 'refunded' => ['Remboursé', 'bg-blue-100 text-blue-800']]; @endphp
<x-layouts.panel space="client" title="Paiements" heading="Mes paiements">
    <x-kpi class="mb-6 max-w-xs" label="Total payé" :value="Fmt::price($total)" icon="credit-card" />
    @if (!$payments)
        <x-empty title="Aucun paiement" icon="credit-card" />
    @else
        <div class="table-wrap"><table class="table">
            <thead><tr><th>Date</th><th>Établissement</th><th>Moyen</th><th>Statut</th><th class="text-right">Montant</th></tr></thead>
            <tbody>@foreach ($payments as $p)
                @php [$l, $c] = $st[$p['status']] ?? [$p['status'], 'bg-gray-100 text-gray-700']; @endphp
                <tr>
                    <td>{{ Fmt::date($p['paid_at'] ?? $p['created_at']) }}</td>
                    <td>{{ $p['accommodation']['name'] ?? '—' }}<span class="block text-xs text-gray-500">{{ $p['reference'] }}</span></td>
                    <td>{{ $p['payment_method'] }}</td>
                    <td><span class="badge {{ $c }}">{{ $l }}</span></td>
                    <td class="text-right font-medium">{{ Fmt::price($p['amount']) }}</td>
                </tr>
            @endforeach</tbody>
        </table></div>
    @endif
</x-layouts.panel>
