@php
    $general = $errors->get('general');
@endphp
@if (session('success') || session('status'))
    <div role="status" class="mb-4 flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-900/20 dark:text-green-300">
        <x-icon name="check-circle" class="mt-0.5 h-4 w-4 shrink-0" /> <span>{{ session('success') ?? session('status') }}</span>
    </div>
@endif
@if (session('error') || $general)
    <div role="alert" class="mb-4 flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300">
        <x-icon name="alert-triangle" class="mt-0.5 h-4 w-4 shrink-0" />
        <span>{{ session('error') }} @foreach ($general as $m) {{ $m }} @endforeach</span>
    </div>
@endif
