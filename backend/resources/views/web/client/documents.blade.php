<x-layouts.panel space="client" title="Documents" heading="Pièce d'identité">
    <p class="mb-6 max-w-2xl text-sm text-gray-600 dark:text-gray-400">Facultatif : enregistrer votre pièce d'identité accélère votre arrivée à la réception. Elle n'est visible que par l'établissement où vous séjournez.</p>
    @if ($identity['submitted'] ?? false)
        <div class="card mb-6 max-w-2xl">
            <p class="font-semibold">{{ $identity['id_type'] }} n° {{ $identity['id_number'] }}</p>
            <p class="mt-1 text-sm">{{ $identity['verified'] ? 'Vérifiée' : 'En attente de vérification' }}</p>
            <div class="mt-3 flex gap-3">
                @if ($identity['recto_url'])<a href="{{ $identity['recto_url'] }}" target="_blank" class="link text-sm">Voir le recto</a>@endif
                @if ($identity['verso_url'])<a href="{{ $identity['verso_url'] }}" target="_blank" class="link text-sm">Voir le verso</a>@endif
            </div>
        </div>
    @endif
    <form method="POST" action="{{ route('client.documents.upload') }}" enctype="multipart/form-data" class="card max-w-2xl space-y-4">
        @csrf
        <h2 class="section-title">{{ ($identity['submitted'] ?? false) ? 'Remplacer la pièce' : 'Ajouter une pièce' }}</h2>
        <div class="grid gap-4 sm:grid-cols-2">
            <x-select name="id_type" label="Type de pièce" :options="['CNI' => "Carte nationale d'identité", 'Passeport' => 'Passeport', 'Permis' => 'Permis de conduire']" :value="$identity['id_type'] ?? 'CNI'" required />
            <x-input name="id_number" label="Numéro" :value="$identity['id_number'] ?? null" required />
        </div>
        <x-input name="id_document_recto" type="file" label="Photo du recto" accept="image/jpeg,image/png" hint="JPG ou PNG, 5 Mo maximum." required />
        <x-input name="id_document_verso" type="file" label="Photo du verso" accept="image/jpeg,image/png" hint="Obligatoire pour la carte d'identité et le permis." />
        <button class="btn-primary" type="submit">Enregistrer</button>
    </form>
</x-layouts.panel>
