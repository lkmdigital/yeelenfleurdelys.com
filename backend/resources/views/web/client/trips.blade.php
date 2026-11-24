<x-layouts.panel space="client" title="Mes séjours" heading="Mes séjours">
    <h2 class="section-title mb-3">À venir</h2>
    <div class="space-y-3">
        @forelse ($upcoming as $b) @include('web.client._booking-row', ['b' => $b]) @empty <p class="text-sm text-gray-500">Aucun séjour à venir.</p> @endforelse
    </div>
    <h2 class="section-title mb-3 mt-8">Passés</h2>
    <div class="space-y-3">
        @forelse ($past as $b) @include('web.client._booking-row', ['b' => $b]) @empty <p class="text-sm text-gray-500">Aucun séjour passé.</p> @endforelse
    </div>
</x-layouts.panel>
