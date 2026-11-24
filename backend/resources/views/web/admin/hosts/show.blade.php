@php use App\Support\Fmt; $req = $h['compliance_requirements'] ?? []; @endphp
<x-layouts.panel space="admin" :title="$h['name']">
    <a href="{{ route('admin.hosts') }}" class="link mb-3 inline-flex items-center gap-1 text-sm"><x-icon name="chevron-left" /> Gérants</a>
    <div class="mb-5 flex flex-wrap items-center gap-3"><h1 class="page-title">{{ $h['name'] }}</h1>@include('web.admin.hosts._compliance', ['status' => $h['compliance_status'] ?? null])</div>
    <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
        <div class="space-y-6">
            <section class="card">
                <h2 class="section-title">Coordonnées</h2>
                <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">E-mail</dt><dd class="break-all font-medium">{{ $h['email'] }}</dd></div>
                    <div><dt class="text-gray-500">Téléphone</dt><dd class="font-medium">{{ $h['phone'] ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">WhatsApp</dt><dd class="font-medium">{{ $h['whatsapp'] ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">RCCM</dt><dd class="font-medium">{{ $h['rccm'] ?: '—' }}</dd></div>
                    <div><dt class="text-gray-500">Banque</dt><dd class="font-medium">{{ $h['bank_name'] ?: '—' }}@if ($h['bank_account_holder']) · {{ $h['bank_account_holder'] }}@endif</dd></div>
                    <div><dt class="text-gray-500">Dernière connexion</dt><dd class="font-medium">{{ $h['last_login_at'] ? Fmt::datetime($h['last_login_at']) : 'Jamais' }}</dd></div>
                </dl>
            </section>
            <section class="card">
                <h2 class="section-title">Pièces du dossier</h2>
                <ul class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                    @foreach ($req as $key => $r)
                        @php $ok = is_array($r) ? ($r['ok'] ?? $r['provided'] ?? $r['met'] ?? false) : (bool) $r; $label = is_array($r) ? ($r['label'] ?? $key) : $key; @endphp
                        <li class="flex items-start gap-2"><x-icon :name="$ok ? 'check-circle' : 'circle'" class="mt-0.5 h-4 w-4 shrink-0 {{ $ok ? 'text-green-600' : 'text-gray-400' }}" /> {{ $label }}</li>
                    @endforeach
                </ul>
            </section>
            <section class="card">
                <h2 class="section-title">Établissement</h2>
                @forelse ($h['accommodations'] ?? [] as $a)
                    <p class="mt-2 text-sm"><a class="link" href="{{ route('admin.accommodations.show', $a['id']) }}">{{ $a['name'] }}</a> <span class="text-gray-500">· {{ $a['city'] ?? '' }}</span></p>
                @empty
                    <p class="mt-2 text-sm text-gray-500">Aucun établissement rattaché. <a class="link" href="{{ route('admin.accommodations.create') }}">En créer un</a></p>
                @endforelse
            </section>
        </div>
        <aside class="space-y-4">
            <section class="card space-y-3">
                <h2 class="section-title">Décision sur le dossier</h2>
                <form method="POST" action="{{ route('admin.hosts.act', [$h['id'], 'validate']) }}">@csrf<button class="btn-primary w-full" type="submit">Valider le dossier</button></form>
                <form method="POST" action="{{ route('admin.hosts.act', [$h['id'], 'reject']) }}" class="space-y-2">@csrf<x-textarea name="comment" label="Motif du refus" rows="2" /><button class="btn-secondary w-full" type="submit">Refuser</button></form>
                <x-modal-confirm :action="route('admin.hosts.act', [$h['id'], 'suspend'])" title="Suspendre ce gérant ?" confirm="Suspendre" button="Suspendre le gérant" button-class="btn-ghost w-full text-red-700" danger>Le gérant ne pourra plus gérer son établissement tant qu’il est suspendu.</x-modal-confirm>
            </section>
            <a href="{{ route('admin.users.show', $h['id']) }}" class="btn-secondary w-full">Gérer le compte (mot de passe, blocage)</a>
        </aside>
    </div>
</x-layouts.panel>
