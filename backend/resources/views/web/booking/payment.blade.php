@php
    use App\Support\Fmt;
    $opts = $b['payment_options']['options'] ?? ($options['options'] ?? []);
    $fullOnly = $b['payment_options']['full_only'] ?? ($options['full_only'] ?? true);
    $reason = $b['payment_options']['reason'] ?? ($options['reason'] ?? null);
    $methods = array_values(array_filter($methods, fn ($m) => $m['is_active'] ?? true));
@endphp
<x-layouts.public title="Paiement">
    <div class="mx-auto max-w-5xl px-4 py-8">
        <h1 class="page-title">Paiement de votre réservation</h1>
        <div class="mt-6 grid items-start gap-8 lg:grid-cols-[1fr_340px]">
            <div class="min-w-0">
                <x-flash />
                @if ($waiting)
                    <div class="card" x-data="paymentWait(@js(route('bookings.payment.status', ['ref' => $ref, 'payment' => $waiting['id']])), {{ $waiting['since'] }})">
                        <h2 class="section-title">Validez le paiement</h2>
                        @if ($waiting['mode'] === 'otp')
                            <p class="mt-2 text-sm">Saisissez le code reçu par SMS pour confirmer le paiement.</p>
                            <form method="POST" action="{{ route('bookings.pay', $ref) }}" class="mt-4 flex gap-3">
                                @csrf
                                <input name="otp_code" class="input max-w-[200px]" inputmode="numeric" autocomplete="one-time-code" required aria-label="Code reçu par SMS">
                                <button class="btn-primary" type="submit">Valider</button>
                            </form>
                        @else
                            <p class="mt-2 text-sm">Une demande de paiement a été envoyée sur votre téléphone. Validez-la avec votre code secret Mobile Money.</p>
                        @endif
                        <p class="mt-4 flex items-center gap-2 text-sm text-gray-600" x-show="!failed"><x-icon name="loader-circle" class="h-4 w-4 animate-spin" /> En attente de la confirmation…</p>
                        <p class="mt-4 text-sm text-red-700" x-cloak x-show="failed" x-text="failed"></p>
                        <a href="{{ route('bookings.payment', $ref) }}" class="link mt-3 inline-block text-sm">Choisir un autre moyen de paiement</a>
                    </div>
                @else
                    <form method="POST" action="{{ route('bookings.pay', $ref) }}" class="space-y-6" x-data="{ busy: false }" @submit="busy = true">
                        @csrf
                        <section class="card">
                            <h2 class="section-title">Montant à payer</h2>
                            <div class="mt-4 space-y-3">
                                @foreach (['full', 'guarantee'] as $type)
                                    @continue(empty($opts[$type]) || ($type === 'guarantee' && $fullOnly))
                                    @php $o = $opts[$type]; @endphp
                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4 has-[:checked]:border-primary has-[:checked]:bg-primary/5 dark:border-gray-700">
                                        <input type="radio" name="payment_type" value="{{ $type }}" @checked(old('payment_type', 'full') === $type) class="mt-1 accent-[rgb(var(--brand))]">
                                        <span class="flex-1">
                                            <span class="flex flex-wrap justify-between gap-2 font-semibold"><span>{{ $o['label'] }}</span><span>{{ Fmt::price($o['amount']) }}</span></span>
                                            @if (!empty($o['original_amount']) && $o['original_amount'] > $o['amount'])<span class="text-sm text-gray-500 line-through">{{ Fmt::price($o['original_amount']) }}</span>@endif
                                            <span class="mt-1 block text-sm text-gray-600 dark:text-gray-400">{{ $o['description'] ?? '' }}</span>
                                            @if (!empty($o['balance_at_hotel']))<span class="mt-1 block text-sm">Solde à régler à l'arrivée : <strong>{{ Fmt::price($o['balance_at_hotel']) }}</strong></span>@endif
                                        </span>
                                    </label>
                                @endforeach
                                @if ($fullOnly && $reason)<p class="text-sm text-gray-600">{{ $reason }}</p>@endif
                            </div>
                        </section>
                        <section class="card">
                            <h2 class="section-title">Moyen de paiement</h2>
                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($methods as $i => $m)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-gray-200 p-3 has-[:checked]:border-primary has-[:checked]:bg-primary/5 dark:border-gray-700">
                                        <input type="radio" name="payment_method" value="{{ $m['slug'] }}" @checked(old('payment_method', $methods[0]['slug'] ?? null) === $m['slug']) class="accent-[rgb(var(--brand))]">
                                        @if (!empty($m['icon']))<span class="flex h-8 w-12 items-center justify-center rounded bg-white"><img src="{{ $m['icon'] }}" alt="" class="max-h-6 max-w-[44px] object-contain"></span>@endif
                                        <span class="text-sm font-medium">{{ $m['name'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('payment_method')<p class="field-error">{{ $message }}</p>@enderror
                        </section>
                        <button type="submit" class="btn-primary w-full" :disabled="busy">
                            <span x-show="!busy">Payer maintenant</span><span x-cloak x-show="busy">Redirection vers le paiement sécurisé…</span>
                        </button>
                        <p class="flex items-center justify-center gap-2 text-xs text-gray-500"><x-icon name="lock" class="h-3.5 w-3.5" /> Paiement sécurisé. Vous êtes redirigé vers la page de votre opérateur.</p>
                    </form>
                @endif
            </div>
            <aside class="lg:sticky lg:top-20">@include('web.booking._summary', ['b' => $b])</aside>
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('paymentWait', (url, since) => ({
                    failed: null,
                    init() {
                        const tick = async () => {
                            if (Date.now() / 1000 - since > 660) {
                                this.failed = "Le délai de validation est dépassé. Si vous avez validé le paiement, la réservation sera confirmée automatiquement dans quelques minutes ; sinon, réessayez.";
                                return;
                            }
                            try {
                                const r = await fetch(url, { headers: { Accept: 'application/json' } });
                                const d = await r.json();
                                if (d.status === 'completed') { window.location = d.success_url; return; }
                                if (d.status === 'failed') { this.failed = "Le paiement n'a pas abouti : il a été refusé, annulé ou a expiré. Vous pouvez réessayer."; return; }
                            } catch (e) {}
                            setTimeout(tick, 5000);
                        };
                        setTimeout(tick, 5000);
                    },
                }));
            });
        </script>
    @endpush
</x-layouts.public>
