@props(['title' => null, 'description' => null])
<x-layouts.base :title="$title" :description="$description" body-class="flex flex-col">
    @php $user = auth()->user(); @endphp
    <header x-data="{ open: false }" class="sticky top-0 z-40 border-b border-gray-200 bg-white/95 backdrop-blur dark:border-gray-800 dark:bg-gray-950/95">
        <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3" aria-label="Navigation principale">
            <x-logo />
            <div class="hidden items-center gap-6 text-sm font-medium md:flex">
                <a href="{{ route('home') }}#etablissements" class="hover:text-primary">Nos établissements</a>
                <a href="{{ route('accommodations.index') }}" class="hover:text-primary">Réserver</a>
                <a href="{{ route('booking.lookup') }}" class="hover:text-primary">Ma réservation</a>
                <a href="{{ route('contact') }}" class="hover:text-primary">Contact</a>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" class="btn-ghost px-2" @click="$store.theme.toggle()" aria-label="Changer le thème">
                    <x-icon name="moon" class="h-5 w-5 dark:hidden" /><x-icon name="sun" class="hidden h-5 w-5 dark:block" />
                </button>
                @auth
                    <a href="{{ \App\Support\Nav::home($user) }}" class="btn-secondary hidden sm:inline-flex"><x-icon name="layout-grid" /> Mon espace</a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">@csrf<button class="btn-ghost" type="submit">Déconnexion</button></form>
                @else
                    <a href="{{ route('login') }}" class="btn-primary hidden sm:inline-flex"><x-icon name="log-in" /> Se connecter</a>
                @endauth
                <button type="button" class="btn-ghost px-2 md:hidden" @click="open = !open" :aria-expanded="open" aria-label="Menu">
                    <x-icon name="menu" class="h-5 w-5" />
                </button>
            </div>
        </nav>
        <div x-cloak x-show="open" x-collapse class="border-t border-gray-200 px-4 py-3 md:hidden dark:border-gray-800">
            <div class="flex flex-col gap-1 text-sm font-medium">
                <a href="{{ route('home') }}#etablissements" class="rounded px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">Nos établissements</a>
                <a href="{{ route('accommodations.index') }}" class="rounded px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">Réserver</a>
                <a href="{{ route('booking.lookup') }}" class="rounded px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">Ma réservation</a>
                <a href="{{ route('contact') }}" class="rounded px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">Contact</a>
                @auth
                    <a href="{{ \App\Support\Nav::home($user) }}" class="rounded px-2 py-2 hover:bg-gray-100 dark:hover:bg-gray-800">Mon espace</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded px-2 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-800" type="submit">Déconnexion</button></form>
                @else
                    <a href="{{ route('login') }}" class="btn-primary mt-2">Se connecter</a>
                @endauth
            </div>
        </div>
    </header>

    <main id="contenu" class="flex-1">
        {{ $slot }}
    </main>

    @include('web.partials.footer')
</x-layouts.base>
