@php use App\Http\Controllers\Web\Admin\AccommodationController as A; @endphp
<x-layouts.panel space="admin" title="Nouvel établissement">
    <a href="{{ route('admin.accommodations') }}" class="link mb-3 inline-flex items-center gap-1 text-sm"><x-icon name="chevron-left" /> Établissements</a>
    <h1 class="page-title mb-2">Nouvel établissement</h1>
    <p class="mb-5 max-w-2xl text-sm text-gray-600 dark:text-gray-400">Rattachez l’établissement à un compte gérant (créez d’abord le compte dans <a class="link" href="{{ route('admin.users.create') }}">Clients et comptes</a>, rôle « Gérant »). Le gérant complète ensuite photos, chambres et tarifs.</p>
    <form method="POST" action="{{ route('admin.accommodations.store') }}" class="card grid max-w-3xl gap-4 sm:grid-cols-2">
        @csrf
        <x-select name="host_id" label="Compte gérant" :options="$hosts" placeholder="Choisir…" required class="sm:col-span-2" />
        <x-input name="name" label="Nom" required />
        <x-select name="type" label="Type" :options="A::TYPES" value="hotel" />
        <x-input name="address" label="Adresse" required class="sm:col-span-2" />
        <x-input name="city" label="Ville" required />
        <x-input name="whatsapp" label="WhatsApp de l’établissement" />
        <x-input name="latitude" type="number" step="any" label="Latitude" value="5.3600" required hint="Repérez le point sur Google Maps (clic droit)." />
        <x-input name="longitude" type="number" step="any" label="Longitude" value="-4.0083" required />
        <x-input name="price_per_night" type="number" min="0" step="500" label="Prix de base par nuit (FCFA)" required />
        <x-input name="max_guests" type="number" min="1" label="Capacité par chambre" value="2" required />
        <x-textarea name="description" label="Description" rows="5" required class="sm:col-span-2" />
        <div class="sm:col-span-2"><button class="btn-primary" type="submit">Créer l’établissement</button></div>
    </form>
</x-layouts.panel>
