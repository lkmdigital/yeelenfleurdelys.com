@php
    $houses = [
        ['Hôtel Fleur de Lys — Tiassalé', ['07 03 24 44 64', '05 44 54 66 86'], 'https://www.facebook.com/Hotelfleur2lys'],
        ['Résidences Yeelen — Abidjan', ['07 97 57 35 75', '07 48 46 42 94'], 'https://www.facebook.com/profile.php?id=100088978470134'],
    ];
    $payments = [['visa_mastercard.png', 'Visa / Mastercard'], ['wave.png', 'Wave'], ['mtn_momo.png', 'MTN Mobile Money'], ['orange-ci.png', 'Orange Money'], ['djamo.jpeg', 'Djamo']];
@endphp
<footer class="mt-auto bg-primary-dark text-white/80">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-14 md:grid-cols-2 lg:grid-cols-5">
        <div class="max-w-sm lg:col-span-2">
            <x-logo variant="white" />
            <p class="mt-4 text-sm text-white/70">Hôtel Fleur de Lys à Tiassalé et Résidences Yeelen à Abidjan : réservez directement auprès de l'établissement, sans intermédiaire.</p>
        </div>
        <div>
            <h2 class="mb-4 font-semibold text-secondary">Réserver</h2>
            <ul class="space-y-2.5 text-sm">
                <li><a class="text-white/70 hover:text-white" href="{{ route('home') }}#etablissements">Nos établissements</a></li>
                <li><a class="text-white/70 hover:text-white" href="{{ route('booking.lookup') }}">Retrouver ma réservation</a></li>
                <li><a class="text-white/70 hover:text-white" href="{{ route('login') }}">Espace client</a></li>
            </ul>
        </div>
        <div>
            <h2 class="mb-4 font-semibold text-secondary">Aide</h2>
            <ul class="space-y-2.5 text-sm">
                <li><a class="text-white/70 hover:text-white" href="{{ route('faq') }}">FAQ</a></li>
                <li><a class="text-white/70 hover:text-white" href="{{ route('contact') }}">Contactez-nous</a></li>
                <li><a class="text-white/70 hover:text-white" href="{{ route('legal', 'cgu') }}">Conditions générales d'utilisation</a></li>
                <li><a class="text-white/70 hover:text-white" href="{{ route('legal', 'cgv') }}">Conditions générales de vente</a></li>
                <li><a class="text-white/70 hover:text-white" href="{{ route('legal', 'confidentialite') }}">Politique de confidentialité</a></li>
            </ul>
        </div>
        <div>
            <h2 class="mb-4 font-semibold text-secondary">Nous joindre</h2>
            <ul class="space-y-4 text-sm text-white/70">
                @foreach ($houses as [$name, $phones, $fb])
                    <li>
                        <p class="font-medium text-white">{{ $name }}</p>
                        @foreach ($phones as $p)
                            <a href="tel:+225{{ str_replace(' ', '', $p) }}" class="block select-all hover:text-white">{{ $p }}</a>
                        @endforeach
                        <a href="{{ $fb }}" target="_blank" rel="noopener noreferrer" class="mt-1 inline-flex items-center gap-1.5 hover:text-white">Facebook <x-icon name="external-link" class="h-3.5 w-3.5" /></a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 border-t border-white/10 px-4 py-6 md:flex-row">
        <p class="text-sm text-white/60">© {{ date('Y') }} Yeelen &amp; Fleur de Lys. Tous droits réservés.</p>
        <div class="flex flex-wrap items-center gap-2">
            @foreach ($payments as [$src, $alt])
                <span class="flex h-8 items-center justify-center rounded-md bg-white px-2"><img src="/images/payment-methods/{{ $src }}" alt="{{ $alt }}" class="h-5 w-auto object-contain"></span>
            @endforeach
        </div>
    </div>
</footer>
