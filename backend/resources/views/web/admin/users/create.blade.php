@php use App\Http\Controllers\Web\Admin\UserController as U; @endphp
<x-layouts.panel space="admin" title="Créer un compte">
    <a href="{{ route('admin.users') }}" class="link mb-3 inline-flex items-center gap-1 text-sm"><x-icon name="chevron-left" /> Clients et comptes</a>
    <h1 class="page-title mb-2">Créer un compte</h1>
    <p class="mb-5 max-w-2xl text-sm text-gray-600 dark:text-gray-400">La personne reçoit un e-mail pour choisir son mot de passe. Aucun mot de passe n’est saisi ici.</p>
    <form method="POST" action="{{ route('admin.users.store') }}" class="card grid max-w-2xl gap-4 sm:grid-cols-2">
        @csrf
        <x-input name="name" label="Nom complet" required />
        <x-input name="email" type="email" label="E-mail" required />
        <x-input name="phone" label="Téléphone" />
        <x-select name="role" label="Rôle" :options="U::ROLES" value="host" />
        <div class="sm:col-span-2"><button class="btn-primary" type="submit">Créer le compte</button></div>
    </form>
</x-layouts.panel>
