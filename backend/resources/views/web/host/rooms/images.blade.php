@php use App\Support\Fmt; @endphp
<x-layouts.panel space="host" :title="'Photos · ' . $room['name']">
    <a href="{{ route('host.rooms') }}" class="link mb-3 inline-flex items-center gap-1 text-sm"><x-icon name="chevron-left" /> Chambres</a>
    <h1 class="page-title mb-4">{{ $room['name'] }}</h1>
    @include('web.host.rooms._tabs')
    <section class="card">
        <form method="POST" action="{{ route('host.rooms.images.upload', $room['id']) }}" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
            @csrf
            <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required class="text-sm" aria-label="Choisir des photos">
            <button class="btn-primary btn-sm" type="submit"><x-icon name="upload" /> Ajouter les photos</button>
        </form>
        @error('images')<p class="field-error">{{ $message }}</p>@enderror
        @error('images.*')<p class="field-error">{{ $message }}</p>@enderror
        <p class="hint">10 Mo maximum par photo. Montrez le lit, la salle de bain et la vue.</p>
        @if (count($room['images'] ?? []))
            <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($room['images'] as $img)
                    <figure class="overflow-hidden rounded-lg border border-gray-200 dark:border-gray-800">
                        <img src="{{ Fmt::img($img['url'] ?? null) }}" alt="" class="aspect-[4/3] w-full object-cover" loading="lazy">
                        <figcaption class="flex items-center justify-between gap-1 p-2">
                            @if ($img['is_primary'] ?? false)
                                <span class="badge bg-secondary text-onaccent">Principale</span>
                            @else
                                <form method="POST" action="{{ route('host.rooms.images.primary', [$room['id'], $img['id']]) }}">@csrf<button class="btn-ghost btn-sm px-2" type="submit">Mettre en avant</button></form>
                            @endif
                            <x-modal-confirm :action="route('host.rooms.images.delete', [$room['id'], $img['id']])" method="DELETE" title="Supprimer cette photo ?" confirm="Supprimer la photo" button="Supprimer" button-class="btn-ghost btn-sm px-2 text-red-700" danger>La photo sera retirée du site.</x-modal-confirm>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        @else
            <x-empty class="mt-4" title="Aucune photo pour cette chambre" icon="image" />
        @endif
    </section>
</x-layouts.panel>
