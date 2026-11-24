@php use App\Support\Fmt; use App\Http\Controllers\Web\Host\RoomController as R; @endphp
<x-layouts.panel space="host" title="Chambres et tarifs">
    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <h1 class="page-title">Chambres et tarifs</h1>
        <a href="{{ route('host.rooms.create') }}" class="btn-primary"><x-icon name="plus" /> Ajouter une chambre</a>
    </div>
    @if ($rooms)
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($rooms as $r)
                @php $cover = Fmt::coverOrNull($r); @endphp
                <article class="card flex flex-col p-0">
                    <div class="relative aspect-[16/9] overflow-hidden rounded-t-xl bg-gray-100 dark:bg-gray-800">
                        @if ($cover)<img src="{{ $cover }}" alt="" class="h-full w-full object-cover" loading="lazy">@else<a href="{{ route('host.rooms.images', $r['id']) }}" class="flex h-full flex-col items-center justify-center gap-1 text-sm text-gray-500 hover:text-primary"><x-icon name="image-plus" class="h-6 w-6" /> Ajouter des photos</a>@endif
                        @unless ($r['is_active'])<span class="badge absolute left-3 top-3 bg-gray-900/80 text-white">Masquée</span>@endunless
                    </div>
                    <div class="flex flex-1 flex-col p-4">
                        <h2 class="font-semibold">{{ $r['name'] }}</h2>
                        <p class="mt-1 text-sm text-gray-500">@if (isset(R::CATEGORIES[$r['room_category'] ?? ''])){{ R::CATEGORIES[$r['room_category']] }} · @endif{{ $r['capacity'] }} pers. · {{ $r['quantity'] ?? 1 }} chambre(s)</p>
                        <p class="mt-2 text-lg font-bold tabular-nums">{{ Fmt::price($r['price_per_night']) }} <span class="text-sm font-normal text-gray-500">/ nuit</span></p>
                        <div class="mt-4 flex flex-wrap gap-2 border-t border-gray-200 pt-3 dark:border-gray-800">
                            <a href="{{ route('host.rooms.edit', $r['id']) }}" class="btn-secondary btn-sm"><x-icon name="pencil" /> Modifier</a>
                            <a href="{{ route('host.rooms.images', $r['id']) }}" class="btn-ghost btn-sm"><x-icon name="image" /> Photos ({{ count($r['images'] ?? []) }})</a>
                            <a href="{{ route('host.rooms.calendar', $r['id']) }}" class="btn-ghost btn-sm"><x-icon name="calendar-days" /> Calendrier</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <x-empty title="Aucune chambre enregistrée" icon="bed-double">Ajoutez chaque type de chambre avec son prix et le nombre de chambres identiques.
            <x-slot:action><a href="{{ route('host.rooms.create') }}" class="btn-primary">Ajouter une chambre</a></x-slot:action>
        </x-empty>
    @endif
</x-layouts.panel>
