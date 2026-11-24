<x-layouts.panel space="client" title="Favoris" heading="Mes favoris">
    @if (!$items)
        <x-empty title="Aucun favori pour le moment" icon="heart">Ajoutez un établissement à vos favoris depuis sa fiche.</x-empty>
    @else
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($items as $e)
                <div>
                    @include('web.partials.establishment-card', ['e' => $e])
                    <form method="POST" action="{{ route('favorites.toggle', $e['id']) }}" class="mt-2">@csrf<button class="link text-sm" type="submit">Retirer des favoris</button></form>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.panel>
