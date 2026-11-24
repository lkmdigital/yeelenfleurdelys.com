<x-layouts.panel space="host" title="Programme fidélité" heading="Programme fidélité">
    @php $joined = !empty($acc['loyalty_program_joined_at']); @endphp
    <div class="grid max-w-4xl gap-6 lg:grid-cols-[1fr_300px]">
        <section class="card space-y-3 text-sm">
            <h2 class="section-title">Comment ça marche</h2>
            <p>Les clients inscrits gagnent des points à chaque séjour terminé dans les établissements Yeelen & Fleur de Lys. Avec leurs points, ils obtiennent des bons de réduction utilisables lors d’une prochaine réservation.</p>
            <p>Participer permet de faire revenir vos clients et d’apparaître dans les offres réservées aux membres.</p>
        </section>
        <section class="card">
            <p class="text-sm text-gray-500">Votre établissement</p>
            <p class="mt-1 text-lg font-bold">{{ $joined ? 'Participe au programme' : 'Ne participe pas' }}</p>
            @if ($joined)<p class="text-xs text-gray-500">Depuis le {{ \App\Support\Fmt::date($acc['loyalty_program_joined_at']) }}</p>@endif
            <form method="POST" action="{{ route('host.programme.update') }}" class="mt-4">
                @csrf @method('PUT')
                <input type="hidden" name="join" value="{{ $joined ? 0 : 1 }}">
                <button class="{{ $joined ? 'btn-secondary' : 'btn-primary' }} w-full" type="submit">{{ $joined ? 'Quitter le programme' : 'Rejoindre le programme' }}</button>
            </form>
        </section>
    </div>
</x-layouts.panel>
