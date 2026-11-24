@php
    $map = ['conforme' => ['Dossier complet', 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'], 'non_conforme' => ['Dossier incomplet', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'], 'complete' => ['Complet', 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'], 'verified' => ['Validé', 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'], 'incomplete' => ['Incomplet', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'], 'pending' => ['À vérifier', 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'], 'rejected' => ['Refusé', 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300']];
    [$t, $c] = $map[$status] ?? [($status ? ucfirst($status) : '—'), 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'];
@endphp
<span class="badge {{ $c }}">{{ $t }}</span>
