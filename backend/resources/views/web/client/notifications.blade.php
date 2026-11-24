@php use App\Support\Fmt; @endphp
<x-layouts.panel space="client" title="Notifications" heading="Notifications">
    @if ($unread)
        <form method="POST" action="{{ route('client.notifications.read-all') }}" class="mb-4">@csrf<button class="btn-secondary btn-sm" type="submit"><x-icon name="check" /> Tout marquer comme lu</button></form>
    @endif
    <div class="card divide-y divide-gray-200 p-0 dark:divide-gray-800">
        @forelse ($items as $n)
            <div class="flex gap-3 px-4 py-3 text-sm">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $n['read_at'] ? 'bg-transparent' : 'bg-secondary' }}"></span>
                <span class="flex-1 {{ $n['read_at'] ? 'text-gray-600 dark:text-gray-400' : 'font-medium' }}">{{ $n['message'] }}<span class="mt-0.5 block text-xs font-normal text-gray-500">{{ Fmt::datetime($n['created_at']) }}</span></span>
            </div>
        @empty
            <p class="px-4 py-10 text-center text-sm text-gray-500">Aucune notification.</p>
        @endforelse
    </div>
</x-layouts.panel>
