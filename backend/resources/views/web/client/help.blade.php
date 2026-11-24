<x-layouts.panel space="client" title="Aide" heading="Aide">
    <div class="grid max-w-4xl gap-4 sm:grid-cols-3">
        <a href="{{ $support['whatsappHref'] }}" target="_blank" rel="noopener noreferrer" class="card block hover:border-primary"><x-icon name="message-circle" class="mb-2 h-5 w-5 text-primary" /><p class="font-semibold">WhatsApp</p><p class="text-sm text-gray-500">Réponse rapide</p></a>
        <a href="{{ $support['phoneHref'] }}" class="card block hover:border-primary"><x-icon name="phone" class="mb-2 h-5 w-5 text-primary" /><p class="font-semibold">Téléphone</p><p class="text-sm text-gray-500">{{ $support['phone'] }}</p></a>
        <a href="{{ route('contact') }}" class="card block hover:border-primary"><x-icon name="mail" class="mb-2 h-5 w-5 text-primary" /><p class="font-semibold">Écrire</p><p class="text-sm text-gray-500">Formulaire de contact</p></a>
    </div>
    <div class="mt-8 max-w-4xl space-y-6">
        @foreach ($groups as $g)
            @continue($g['slug'] === 'partenaires')
            <section>
                <h2 class="section-title mb-2">{{ $g['title'] }}</h2>
                <div class="divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-900">
                    @foreach ($g['items'] as $item)
                        <details class="group px-5 py-3"><summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-sm font-medium">{{ $item['q'] }}<span class="text-lg text-primary transition-transform group-open:rotate-45">+</span></summary><p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $item['a'] }}</p></details>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-layouts.panel>
