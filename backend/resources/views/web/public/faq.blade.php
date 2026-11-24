<x-support-shell title="Questions fréquentes" intro="Les réponses aux questions les plus courantes sur la réservation, le paiement et l'annulation.">
    <nav class="mb-10 flex flex-wrap gap-2" aria-label="Rubriques">
        @foreach ($groups as $g)
            <a href="#{{ $g['slug'] }}" class="rounded-lg border border-gray-300 px-3.5 py-1.5 text-sm hover:border-primary hover:text-primary dark:border-gray-600">{{ $g['title'] }}</a>
        @endforeach
    </nav>
    <div class="space-y-10">
        @foreach ($groups as $g)
            <section id="{{ $g['slug'] }}" class="scroll-mt-24">
                <h2 class="section-title mb-4">{{ $g['title'] }}</h2>
                <div class="divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white dark:divide-gray-700 dark:border-gray-700 dark:bg-gray-900">
                    @foreach ($g['items'] as $item)
                        <details class="group px-5 py-4">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-medium">
                                {{ $item['q'] }} <span class="text-xl leading-none text-primary transition-transform group-open:rotate-45">+</span>
                            </summary>
                            <p class="mt-3 text-sm leading-relaxed text-gray-600 dark:text-gray-400">{{ $item['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
    <div class="mt-12 rounded-xl bg-gray-50 p-6 text-center dark:bg-gray-800/60">
        <p class="mb-1 font-semibold">Vous ne trouvez pas votre réponse ?</p>
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Notre équipe vous répond par WhatsApp, téléphone ou e-mail.</p>
        <div class="flex flex-wrap justify-center gap-3">
            <a href="{{ route('contact') }}" class="btn-primary">Nous contacter</a>
            <a href="{{ $support['whatsappHref'] }}" target="_blank" rel="noopener noreferrer" class="btn-secondary">WhatsApp</a>
        </div>
    </div>
</x-support-shell>
