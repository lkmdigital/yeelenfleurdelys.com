<x-auth-card title="Votre avis sur votre séjour" :subtitle="$booking ? $booking['accommodation_name'] . ($booking['room_name'] ? ' — ' . $booking['room_name'] : '') . ' · du ' . \App\Support\Fmt::date($booking['check_in']) . ' au ' . \App\Support\Fmt::date($booking['check_out']) : null">
    @if (session('success'))
        <p class="text-sm">Vous pouvez fermer cette page. À bientôt !</p>
    @elseif (!$booking)
        <x-empty :title="$problem" icon="link">Si vous pensez qu'il s'agit d'une erreur, contactez-nous.</x-empty>
    @else
        <form method="POST" action="{{ route('review.token.submit', $token) }}" class="space-y-5" x-data="{ rating: {{ (int) old('rating', 0) }} }">
            @csrf
            <fieldset>
                <legend class="label">Note générale <span class="text-red-600">*</span></legend>
                <div class="flex gap-1">
                    @for ($s = 1; $s <= 5; $s++)
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="{{ $s }}" class="sr-only" x-model.number="rating" @checked(old('rating') == $s)>
                            <x-icon name="star" class="h-8 w-8" x-bind:class="rating >= {{ $s }} ? 'fill-current text-secondary' : 'text-gray-300'" />
                            <span class="sr-only">{{ $s }} sur 5</span>
                        </label>
                    @endfor
                </div>
                @error('rating')<p class="field-error">{{ $message }}</p>@enderror
            </fieldset>
            <details class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                <summary class="cursor-pointer text-sm font-medium">Noter en détail (facultatif)</summary>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach ($categories as $key => $label)
                        <x-select :name="'category_ratings[' . $key . ']'" :label="$label" :options="['' => '—', 5 => '5 — Excellent', 4 => '4 — Très bien', 3 => '3 — Correct', 2 => '2 — Décevant', 1 => '1 — Mauvais']" />
                    @endforeach
                </div>
            </details>
            <x-textarea name="comment" label="Votre commentaire" rows="5" maxlength="1000" required />
            <button type="submit" class="btn-primary w-full">Publier mon avis</button>
        </form>
    @endif
</x-auth-card>
