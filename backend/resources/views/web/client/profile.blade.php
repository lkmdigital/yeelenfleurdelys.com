<x-layouts.panel space="client" title="Profil" heading="Mon profil">
    <div class="grid max-w-4xl gap-6 lg:grid-cols-[1fr_260px]">
        <form method="POST" action="{{ route('client.profile.update') }}" class="card space-y-4">
            @csrf @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input name="first_name" label="Prénom" :value="$me['first_name'] ?? null" required />
                <x-input name="last_name" label="Nom" :value="$me['last_name'] ?? null" required />
                <x-input name="phone" type="tel" label="Téléphone" :value="$me['phone'] ?? null" />
                <x-input name="whatsapp" type="tel" label="WhatsApp" :value="$me['whatsapp'] ?? null" />
                <x-input name="nationality" label="Nationalité" :value="$me['nationality'] ?? null" />
                <x-input name="date_of_birth" type="date" label="Date de naissance" :value="isset($me['date_of_birth']) ? substr($me['date_of_birth'], 0, 10) : null" />
                <x-input name="residence_country" label="Pays de résidence" :value="$me['residence_country'] ?? null" />
                <x-input name="residence_city" label="Ville" :value="$me['residence_city'] ?? null" />
            </div>
            <p class="text-sm text-gray-500">Adresse e-mail : {{ $me['email'] ?? '' }}</p>
            <button class="btn-primary" type="submit">Enregistrer</button>
        </form>
        <form method="POST" action="{{ route('client.profile.avatar') }}" enctype="multipart/form-data" class="card space-y-3 self-start">
            @csrf
            <div class="flex justify-center">
                @if (!empty($me['avatar']))
                    <img src="{{ \App\Support\Fmt::img($me['avatar']) }}" alt="" class="h-24 w-24 rounded-full object-cover">
                @else
                    <span class="flex h-24 w-24 items-center justify-center rounded-full bg-primary text-2xl font-bold text-white">{{ \App\Support\Fmt::initials($me['name'] ?? '') }}</span>
                @endif
            </div>
            <x-input name="avatar" type="file" label="Photo de profil" accept="image/jpeg,image/png" hint="2 Mo maximum." required />
            <button class="btn-secondary w-full" type="submit">Changer la photo</button>
        </form>
    </div>
</x-layouts.panel>
