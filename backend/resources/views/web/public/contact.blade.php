<x-support-shell title="Nous contacter" intro="Une question sur une réservation ou un établissement ? Écrivez-nous, nous répondons rapidement.">
    <div class="grid gap-8 md:grid-cols-[1fr_280px]">
        <form method="POST" action="{{ route('contact.send') }}" class="card space-y-4">
            @csrf
            <div class="hidden" aria-hidden="true"><label>Ne pas remplir <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-input name="name" label="Nom" :value="auth()->user()?->name" autocomplete="name" required />
                <x-input name="email" type="email" label="E-mail" :value="auth()->user()?->email" autocomplete="email" required />
            </div>
            <x-input name="subject" label="Sujet" />
            <x-textarea name="message" label="Message" rows="6" required />
            <button type="submit" class="btn-primary">Envoyer le message</button>
        </form>
        <div class="order-first space-y-4 text-sm md:order-none">
            <div class="card">
                <p class="font-semibold">Hôtel Fleur de Lys — Tiassalé</p>
                <a href="tel:+2250703244464" class="flex items-center gap-2 py-2 font-medium text-primary hover:underline"><x-icon name="phone" class="h-4 w-4" /> 07 03 24 44 64</a>
                <a href="tel:+2250544546686" class="flex items-center gap-2 py-2 font-medium text-primary hover:underline"><x-icon name="phone" class="h-4 w-4" /> 05 44 54 66 86</a>
            </div>
            <div class="card">
                <p class="font-semibold">Résidences Yeelen — Abidjan</p>
                <a href="tel:+2250797573575" class="flex items-center gap-2 py-2 font-medium text-primary hover:underline"><x-icon name="phone" class="h-4 w-4" /> 07 97 57 35 75</a>
                <a href="tel:+2250748469442" class="flex items-center gap-2 py-2 font-medium text-primary hover:underline"><x-icon name="phone" class="h-4 w-4" /> 07 48 46 42 94</a>
            </div>
            <div class="card">
                <p class="font-semibold">Assistance</p>
                <a href="{{ $support['whatsappHref'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-2 py-2 font-medium text-primary hover:underline"><x-icon name="message-circle" class="h-4 w-4" /> WhatsApp</a>
                <a href="{{ $support['emailHref'] }}" class="flex items-center gap-2 break-all py-2 font-medium text-primary hover:underline"><x-icon name="mail" class="h-4 w-4 shrink-0" /> {{ $support['email'] }}</a>
            </div>
        </div>
    </div>
</x-support-shell>
