@php use App\Support\Fmt; @endphp
<x-layouts.panel space="admin" title="Paiements" heading="Paiements des clients">
    <div class="grid gap-4 sm:grid-cols-3">
        <x-kpi label="Encaissé" :value="Fmt::price($summary['total_completed'] ?? 0)" icon="check-circle" :hint="($summary['count_completed'] ?? 0) . ' paiement(s)'" />
        <x-kpi label="Échoués" :value="Fmt::price($summary['total_failed'] ?? 0)" icon="x-circle" :hint="($summary['count_failed'] ?? 0) . ' tentative(s)'" />
        <x-kpi label="Remboursé" :value="Fmt::price($summary['total_refunded'] ?? 0)" icon="rotate-ccw" />
    </div>

    @if ($stuck)
        <section class="mt-6">
            <h2 class="section-title mb-1">Paiements bloqués « en attente »</h2>
            <p class="mb-3 text-sm text-gray-600 dark:text-gray-400">Le client a peut-être payé sans que la confirmation arrive. Vérifiez auprès du prestataire, puis relancez la vérification ou confirmez à la main.</p>
            <div class="space-y-3">
                @foreach ($stuck as $p)
                    <div class="card flex flex-wrap items-center justify-between gap-3 text-sm" x-data="{ open: false }">
                        <div><span class="font-semibold">{{ Fmt::price($p['amount']) }}</span> · {{ $p['user']['name'] ?? 'Client' }} · {{ $p['booking']['accommodation']['name'] ?? '' }}<span class="block text-xs text-gray-500">{{ Fmt::datetime($p['created_at']) }} · {{ $p['payment_method'] ?? '' }} @if ($p['transaction_id'] ?? null)· réf. {{ $p['transaction_id'] }}@endif</span></div>
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('admin.payments.act', [$p['id'], 'reconcile']) }}">@csrf<button class="btn-secondary btn-sm" type="submit">Revérifier</button></form>
                            <button type="button" class="btn-ghost btn-sm" @click="open = !open">Confirmer à la main</button>
                        </div>
                        <form x-show="open" x-cloak method="POST" action="{{ route('admin.payments.act', [$p['id'], 'confirm']) }}" class="flex w-full flex-wrap items-end gap-2">
                            @csrf
                            <x-input name="transaction_id" :id="'tx' . $p['id']" label="Référence de la transaction" required />
                            <x-input name="note" :id="'nt' . $p['id']" label="Note" />
                            <button class="btn-primary btn-sm" type="submit">Confirmer le paiement</button>
                        </form>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mt-6">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <h2 class="section-title">Historique</h2>
            <nav class="flex gap-1 text-sm">
                @foreach (['' => 'Tous', 'completed' => 'Réussis', 'failed' => 'Échoués'] as $k => $l)
                    <a href="{{ route('admin.payments', array_filter(['status' => $k])) }}" class="rounded-lg px-3 py-2 font-medium {{ ($status ?? '') === $k ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800' }}">{{ $l }}</a>
                @endforeach
            </nav>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50"><tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Client</th><th class="px-4 py-3">Établissement</th><th class="px-4 py-3">Moyen</th><th class="px-4 py-3 text-right">Montant</th><th class="px-4 py-3">Statut</th></tr></thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($payments as $p)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3">{{ Fmt::datetime($p['created_at']) }}</td>
                            <td class="px-4 py-3">{{ $p['user']['name'] ?? '—' }}</td>
                            <td class="px-4 py-3">@if ($p['booking'] ?? null)<a class="link" href="{{ route('admin.reservations.show', $p['booking']['id']) }}">{{ $p['booking']['accommodation']['name'] ?? 'Réservation' }}</a>@else — @endif</td>
                            <td class="px-4 py-3">{{ $p['payment_method'] ?? '' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ Fmt::price($p['amount']) }}</td>
                            <td class="px-4 py-3">{{ Fmt::paymentStatus($p['status']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Aucun paiement.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pager :meta="$meta" />
    </section>
</x-layouts.panel>
