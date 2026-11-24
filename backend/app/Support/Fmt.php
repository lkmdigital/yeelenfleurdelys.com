<?php

namespace App\Support;

use Carbon\Carbon;

/** Formats d'affichage partagés par les vues Blade. */
class Fmt
{
    public const BOOKING_STATUS = [
        'awaiting_host_confirmation' => ['À confirmer par l’établissement', 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'],
        'pending' => ['En attente de paiement', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'],
        'confirmed' => ['Confirmée', 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'],
        'cancelled' => ['Annulée', 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'],
        'completed' => ['Terminée', 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'],
        'rejected' => ['Refusée', 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'],
        'no_show' => ['Non présenté', 'bg-gray-200 text-gray-800 dark:bg-gray-800 dark:text-gray-300'],
    ];

    public const PAYMENT_STATUS = [
        'pending' => 'Non payé', 'unpaid' => 'Non payé', 'partial' => 'Garantie payée', 'deposit_paid' => 'Garantie payée',
        'paid' => 'Payé', 'failed' => 'Échec', 'refunded' => 'Remboursé', 'partially_refunded' => 'Remboursé en partie',
    ];

    public const ACCOMMODATION_STATUS = [
        'published' => ['En ligne', 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300'],
        'pending' => ['En attente', 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300'],
        'rejected' => ['Refusé', 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'],
        'unavailable' => ['Indisponible', 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300'],
        'renovation' => ['En rénovation', 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300'],
        'draft' => ['Brouillon', 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300'],
        'disabled' => ['Désactivé', 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300'],
        'removed' => ['Retiré', 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300'],
    ];

    public const TYPES = ['hotel' => 'Hôtel', 'lodge' => 'Écolodge', 'guesthouse' => "Maison d'hôtes", 'apartment' => 'Résidence', 'other' => 'Autre'];

    public static function price(mixed $amount, bool $currency = true): string
    {
        $n = is_numeric($amount) ? (float) $amount : 0;
        $s = number_format(round($n), 0, ',', ' ');
        return $currency ? $s . ' FCFA' : $s;
    }

    public static function date(mixed $value, string $format = 'd/m/Y'): string
    {
        if (!$value) return '—';
        try {
            return Carbon::parse($value)->locale('fr')->translatedFormat($format);
        } catch (\Throwable) {
            return (string) $value;
        }
    }

    public static function dateLong(mixed $value): string
    {
        return self::date($value, 'D j M Y');
    }

    public static function datetime(mixed $value): string
    {
        return self::date($value, 'd/m/Y H:i');
    }

    public static function nights(mixed $in, mixed $out): int
    {
        try {
            return max(0, Carbon::parse($in)->startOfDay()->diffInDays(Carbon::parse($out)->startOfDay()));
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function bookingStatus(?string $status, ?string $label = null): array
    {
        $cfg = self::BOOKING_STATUS[$status] ?? [$status ?? '—', 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'];
        return [$label ?: $cfg[0], $cfg[1]];
    }

    public static function paymentStatus(?string $status, ?string $label = null): string
    {
        return $label ?: (self::PAYMENT_STATUS[$status] ?? ($status ?? '—'));
    }

    public static function accommodationStatus(?string $status): array
    {
        return self::ACCOMMODATION_STATUS[$status] ?? [$status ?? '—', 'bg-gray-100 text-gray-700'];
    }

    public static function type(?string $type): string
    {
        return self::TYPES[$type] ?? (string) $type;
    }

    /** URL d'image utilisable par le navigateur (même origine que le site). */
    public static function img(?string $url, string $fallback = '/images/no-photo.svg'): string
    {
        if (!$url) return $fallback;
        if (preg_match('#^https?://#', $url)) {
            return preg_replace('#^https?://(localhost|127\.0\.0\.1)(:\d+)?#', '', $url) ?: $url;
        }
        return str_starts_with($url, '/') ? $url : '/storage/' . ltrim($url, '/');
    }

    /** Image principale d'un établissement / d'une chambre (tableau d'API). */
    public static function cover(array $item): string
    {
        $images = $item['images'] ?? [];
        $primary = collect($images)->firstWhere('is_primary', true) ?? ($images[0] ?? null);
        return self::img($primary['url'] ?? ($item['primary_image']['url'] ?? ($item['image'] ?? null)));
    }

    /** Comme cover(), mais null s'il n'y a aucune photo. */
    public static function coverOrNull(array $item): ?string
    {
        $url = self::cover($item);
        return $url === '/images/no-photo.svg' ? null : $url;
    }

    public static function initials(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        return mb_strtoupper(implode('', array_map(fn ($p) => mb_substr($p, 0, 1), array_slice(array_filter($parts), 0, 2)))) ?: 'YF';
    }

    private const AMENITIES = [
        'wifi' => 'Wi-Fi', 'climatisation' => 'Climatisation', 'parking' => 'Parking', 'piscine' => 'Piscine',
        'balcon' => 'Balcon', 'tv' => 'Télévision', 'television' => 'Télévision', 'restaurant' => 'Restaurant',
        'bar' => 'Bar', 'cuisine' => 'Cuisine équipée', 'kitchen' => 'Cuisine équipée', 'security' => 'Sécurité 24 h/24',
        'securite' => 'Sécurité 24 h/24', 'generator' => 'Groupe électrogène', 'groupe_electrogene' => 'Groupe électrogène',
        'room_service' => 'Service en chambre', 'laundry' => 'Blanchisserie', 'breakfast' => 'Petit-déjeuner',
        'air_conditioning' => 'Climatisation', 'pool' => 'Piscine', 'gym' => 'Salle de sport', 'spa' => 'Spa',
    ];

    public static function amenity(string $key): string
    {
        return self::AMENITIES[mb_strtolower($key)] ?? mb_strtoupper(mb_substr($key, 0, 1)) . str_replace('_', ' ', mb_substr($key, 1));
    }

    public static function time(?string $t): string
    {
        return $t ? str_replace(':', ' h ', substr($t, 0, 5)) : '';
    }
}
