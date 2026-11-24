<x-layouts.public title="Réserver">
    <div class="mx-auto max-w-7xl px-4 py-10">
        <h1 class="page-title">Réserver un séjour</h1>
        <form method="GET" class="card mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5 lg:items-end">
            <x-select name="city" label="Ville" :options="['' => 'Toutes les villes'] + $cities->mapWithKeys(fn ($c) => [$c => $c])->all()" :value="request('city')" />
            <x-input name="check_in" type="date" label="Arrivée" :value="request('check_in')" :min="now()->toDateString()" />
            <x-input name="check_out" type="date" label="Départ" :value="request('check_out')" :min="now()->addDay()->toDateString()" />
            <x-input name="guests" type="number" label="Voyageurs" :value="request('guests', 2)" min="1" max="20" />
            <button class="btn-primary" type="submit"><x-icon name="search" /> Rechercher</button>
        </form>

        @if ($failed)
            <p class="mt-8 text-gray-600">Impossible de charger les établissements pour le moment.</p>
        @elseif (!$items)
            <x-empty class="mt-8" title="Aucun établissement disponible pour cette recherche." icon="search">
                Essayez d'autres dates ou une autre ville.
            </x-empty>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $e)
                    @include('web.partials.establishment-card', ['e' => $e])
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.public>
