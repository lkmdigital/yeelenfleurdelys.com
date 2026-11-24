@php use App\Support\Fmt; @endphp
<x-layouts.panel space="host" title="Clients" heading="Clients">
    <form method="GET" class="mb-4 flex max-w-xl gap-2">
        <input name="search" value="{{ $search }}" class="input" placeholder="Nom, e-mail ou téléphone" aria-label="Rechercher un client">
        <button class="btn-primary" type="submit">Rechercher</button>
    </form>
    @if ($clients)
        {{-- Mobile : une carte par client, avec appel et e-mail en un geste. --}}
        <ul class="space-y-3 md:hidden">
            @foreach ($clients as $c)
                <li class="card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold">{{ $c['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $c['bookings_count'] }} séjour(s) · dernier : {{ $c['last_stay'] ? Fmt::date($c['last_stay']) : '—' }}@if ($c['cancelled_count'] ?? 0) · {{ $c['cancelled_count'] }} annulation(s)@endif</p>
                        </div>
                        <p class="shrink-0 font-bold tabular-nums">{{ Fmt::price($c['total_spent'] ?? 0) }}</p>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 border-t border-gray-200 pt-3 dark:border-gray-800">
                        @if ($c['phone'] ?? null)<a href="tel:{{ $c['phone'] }}" class="btn-secondary"><x-icon name="phone" class="h-4 w-4" /> Appeler</a>@endif
                        <a href="mailto:{{ $c['email'] }}" class="btn-ghost {{ ($c['phone'] ?? null) ? '' : 'col-span-2' }}"><x-icon name="mail" class="h-4 w-4" /> E-mail</a>
                    </div>
                </li>
            @endforeach
        </ul>
        <div class="table-wrap hidden md:block">
            <table class="table">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-gray-800/50">
                    <tr><th class="px-4 py-3">Client</th><th class="px-4 py-3">Contact</th><th class="px-4 py-3 text-right">Séjours</th><th class="px-4 py-3 text-right">Total dépensé</th><th class="px-4 py-3">Dernier séjour</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($clients as $c)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $c['name'] }}@if ($c['cancelled_count'] ?? 0)<span class="block text-xs font-normal text-gray-500">{{ $c['cancelled_count'] }} annulation(s)</span>@endif</td>
                            <td class="px-4 py-3"><a class="link block break-all" href="mailto:{{ $c['email'] }}">{{ $c['email'] }}</a>@if ($c['phone'] ?? null)<a class="text-gray-600 hover:underline dark:text-gray-400" href="tel:{{ $c['phone'] }}">{{ $c['phone'] }}</a>@endif</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $c['bookings_count'] }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ Fmt::price($c['total_spent'] ?? 0) }}</td>
                            <td class="px-4 py-3">{{ $c['last_stay'] ? Fmt::date($c['last_stay']) : '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-pager :meta="$meta" />
    @else
        <x-empty :title="$denied ? 'Accès réservé' : ($search ? 'Aucun client trouvé' : 'Aucun client pour le moment')" icon="users">{{ $denied ? 'Votre compte n’a pas accès à la liste des clients.' : '' }}</x-empty>
    @endif
</x-layouts.panel>
