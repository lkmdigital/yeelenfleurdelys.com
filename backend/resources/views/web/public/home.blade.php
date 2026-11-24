<x-layouts.public>
    {{-- Photos d'illustration provisoires (Unsplash, licence libre) : à remplacer par les vraies photos des établissements. --}}
    @php
        $heroPhotos = [
            'photo-1582719508461-905c673771fd',
            'photo-1631049307264-da0ec9d70304',
            'photo-1745429523617-0d837856ca35',
        ];
    @endphp
    <section class="hero relative isolate flex min-h-[520px] items-end overflow-hidden bg-primary text-primary-dark md:min-h-[78vh]">
        @foreach ($heroPhotos as $i => $photo)
            <img src="https://images.unsplash.com/{{ $photo }}?auto=format&fit=crop&w=1920&q=70"
                 srcset="https://images.unsplash.com/{{ $photo }}?auto=format&fit=crop&w=800&q=70 800w, https://images.unsplash.com/{{ $photo }}?auto=format&fit=crop&w=1920&q=70 1920w"
                 sizes="100vw" alt="" aria-hidden="true" @if ($i > 0) loading="lazy" @endif
                 class="hero-slide absolute inset-0 -z-20 h-full w-full object-cover" style="--i: {{ $i }}">
        @endforeach
        {{-- Voile jaune léger, limité au bas de la photo (juste derrière le texte), mobile comme ordinateur. --}}
        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-secondary/85 via-secondary/35 via-30% to-transparent to-60%"></div>

        <div class="mx-auto w-full max-w-7xl px-4 pb-12 md:pb-16">
            <h1 class="max-w-4xl font-title text-4xl font-extrabold leading-[1.05] md:text-7xl">Hôtel Fleur de Lys &amp; Résidences Yeelen</h1>
            <p class="mt-4 text-base font-medium text-primary-dark/80 md:text-lg">Tiassalé · Angré · Riviera Palmeraie · Koumassi</p>
        </div>
    </section>

    <section id="etablissements" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-12 md:py-16">
        <h2 class="section-title text-2xl md:text-3xl">Nos établissements</h2>
        @if ($failed)
            <p class="mt-6 text-gray-600 dark:text-gray-400">Impossible de charger les établissements pour le moment. Réessayez dans quelques instants.</p>
        @elseif (!$items)
            <p class="mt-6 text-gray-600 dark:text-gray-400">Aucun établissement n'est encore publié.</p>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($items as $e)
                    @include('web.partials.establishment-card', ['e' => $e])
                @endforeach
            </div>
        @endif
    </section>
</x-layouts.public>
