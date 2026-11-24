{{-- Bouton + boîte de confirmation (action irréversible nommée). --}}
@props(['action', 'method' => 'POST', 'title', 'confirm' => 'Confirmer', 'button', 'buttonClass' => 'btn-secondary btn-sm', 'danger' => false])
<div x-data="{ open: false }" class="inline">
    <button type="button" class="{{ $buttonClass }}" @click="open = true">{{ $button }}</button>
    <div x-cloak x-show="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @keydown.escape.window="open = false" role="dialog" aria-modal="true">
        <form method="POST" action="{{ $action }}" class="w-full max-w-md rounded-xl bg-white p-6 text-left shadow-xl dark:bg-gray-900" @click.outside="open = false">
            @csrf
            @if (strtoupper($method) !== 'POST') @method($method) @endif
            <h2 class="text-lg font-bold">{{ $title }}</h2>
            <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $slot }}</div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" class="btn-secondary" @click="open = false">Annuler</button>
                <button type="submit" class="{{ $danger ? 'btn-danger' : 'btn-primary' }}">{{ $confirm }}</button>
            </div>
        </form>
    </div>
</div>
