{{-- Espace de gestion (gérant, administration, client) : menu latéral + barre haute. --}}
@props(['title' => null, 'space' => 'host', 'heading' => null])
@php
    $user = auth()->user();
    $isActive = fn ($route) => request()->routeIs($route) || request()->routeIs($route . '.*');
    $groups = match ($space) {
        'admin' => \App\Support\Nav::admin(),
        'client' => ['' => \App\Support\Nav::client()],
        default => ['' => \App\Support\Nav::host($user)],
    };
    $spaceLabel = ['admin' => 'Administration', 'client' => 'Espace client', 'host' => 'Gestion'][$space] ?? '';
@endphp
<x-layouts.base :title="$title" body-class="bg-gray-50 dark:bg-gray-950">
    <div x-data="{ side: false }" class="flex min-h-screen">
        <div x-cloak x-show="side" class="fixed inset-0 z-30 bg-black/40 lg:hidden" @click="side = false"></div>
        <aside :class="side ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-primary-dark text-white/80 transition-transform lg:sticky lg:top-0 lg:h-screen">
            <div class="flex items-center justify-between gap-2 border-b border-white/10 px-4 py-4">
                <x-logo variant="white" size="sm" :href="\App\Support\Nav::home($user)" />
                <button type="button" class="-mr-2 rounded-lg p-2.5 hover:bg-white/10 lg:hidden" @click="side = false" aria-label="Fermer le menu"><x-icon name="x" class="h-5 w-5" /></button>
            </div>
            <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4" aria-label="{{ $spaceLabel }}">
                @foreach ($groups as $group => $items)
                    <div>
                        @if ($group)<p class="mb-1 px-3 text-[11px] font-semibold uppercase tracking-wider text-white/50">{{ $group }}</p>@endif
                        <div class="space-y-0.5">
                            @foreach ($items as $item)
                                @php [$label, $route, $icon] = $item; $on = $isActive($route); @endphp
                                <a href="{{ route($route) }}" @if ($on) aria-current="page" @endif
                                    class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium {{ $on ? 'bg-secondary text-onaccent' : 'hover:bg-white/10 hover:text-white' }}">
                                    <x-icon :name="$icon" class="h-4 w-4 shrink-0" /> <span class="truncate">{{ $label }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
            <div class="space-y-0.5 border-t border-white/10 px-3 py-3 text-sm">
                @if ($space === 'host')
                    <a href="{{ route('host.settings') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10 hover:text-white"><x-icon name="settings" /> Paramètres</a>
                @endif
                <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10 hover:text-white"><x-icon name="globe" /> Voir le site</a>
                <form method="POST" action="{{ route('logout') }}">@csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-lg px-3 py-2 hover:bg-white/10 hover:text-white"><x-icon name="log-out" /> Déconnexion</button>
                </form>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                <button type="button" class="btn-ghost px-2 lg:hidden" @click="side = true" aria-label="Ouvrir le menu"><x-icon name="menu" class="h-5 w-5" /></button>
                <div class="min-w-0 flex-1">
                    @isset($establishment)
                        <span class="badge bg-secondary text-onaccent">{{ $establishment }}</span>
                    @elseif ($space === 'host' && !empty($hostProperty['name']))
                        <span class="inline-flex min-w-0 items-center gap-2"><x-icon name="building-2" class="h-4 w-4 shrink-0 text-primary" /><span class="truncate text-sm font-semibold">{{ $hostProperty['name'] }}</span>@if (!empty($hostProperty['city']))<span class="hidden text-sm text-gray-500 sm:inline">· {{ $hostProperty['city'] }}</span>@endif</span>
                    @else
                        <span class="text-sm font-medium text-gray-500">{{ $spaceLabel }}</span>
                    @endisset
                </div>
                <button type="button" class="btn-ghost px-2" @click="$store.theme.toggle()" aria-label="Changer le thème">
                    <x-icon name="moon" class="h-5 w-5 dark:hidden" /><x-icon name="sun" class="hidden h-5 w-5 dark:block" />
                </button>
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-bold text-white">{{ \App\Support\Fmt::initials($user?->name) }}</span>
                    <span class="hidden text-sm sm:block"><span class="block max-w-[180px] truncate font-medium">{{ $user?->name }}</span></span>
                </div>
            </header>
            <main id="contenu" class="flex-1 p-4 lg:p-6">
                <div class="mx-auto max-w-7xl">
                    <x-flash />
                    @if ($heading)
                        <h1 class="page-title mb-5">{{ $heading }}</h1>
                    @endif
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</x-layouts.base>
