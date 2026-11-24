<?php

namespace App\Support;

use App\Models\User;

/**
 * Menus latéraux des trois espaces (gérant, administration, client).
 * Chaque entrée : [libellé, nom de route, icône, permission gérant éventuelle].
 */
class Nav
{
    public static function host(User $user): array
    {
        $items = static::hostItems();

        if ($user->isStaff()) {
            $perms = $user->staffPermissions();
            $items = array_values(array_filter($items, fn ($i) => $i[3] === null || in_array($i[3], $perms, true)));
        }

        return $items;
    }

    /** Toutes les entrées du menu gérant, sans filtrage. */
    public static function hostItems(): array
    {
        return [
            ['Tableau de bord', 'host.dashboard', 'layout-grid', null],
            ['Mon établissement', 'host.property', 'building-2', 'property'],
            ['Chambres et tarifs', 'host.rooms', 'bed-double', 'rooms'],
            ['Calendrier', 'host.calendar', 'calendar-days', 'calendar'],
            ['Réservations', 'host.reservations', 'book-open', 'reservations'],
            ['Demandes', 'host.requests', 'calendar-check', 'reservations'],
            ['Clients', 'host.clients', 'users', 'clients'],
            ['Messages', 'host.inbox', 'inbox', 'reservations'],
            ['Avis', 'host.reviews', 'star', 'reviews'],
            ['Promotions', 'host.promotions', 'gift', 'promotions'],
            ['Finances', 'host.finances', 'wallet', 'finances'],
            ['Statistiques', 'host.stats', 'bar-chart-3', 'stats'],
            ['Programme fidélité', 'host.programme', 'award', 'stats'],
            ['Utilisateurs', 'host.staff', 'user-cog', 'staff'],
        ];
    }

    public static function admin(): array
    {
        return [
            'Principal' => [
                ['Vue globale', 'admin.dashboard', 'layout-grid'],
                ['Statistiques', 'admin.analytics', 'bar-chart-3'],
            ],
            'Établissements' => [
                ['Établissements', 'admin.accommodations', 'building-2'],
                ['Gérants', 'admin.hosts', 'handshake'],
                ['Réservations', 'admin.reservations', 'calendar-check'],
                ['Clients et comptes', 'admin.users', 'users'],
                ['Avis clients', 'admin.reviews', 'star'],
                ['Promotions', 'admin.promotions', 'gift'],
                ['Programme fidélité', 'admin.programme', 'award'],
            ],
            'Finance' => [
                ['Comptabilité', 'admin.accounting', 'wallet'],
                ['Paiements', 'admin.payments', 'credit-card'],
            ],
            'Système' => [
                ['Paramètres', 'admin.settings', 'settings'],
                ['Mentions légales', 'admin.legal', 'scale'],
                ["Journal d'activité", 'admin.activity', 'history'],
            ],
        ];
    }

    public static function client(): array
    {
        return [
            ['Tableau de bord', 'client.dashboard', 'layout-grid'],
            ['Mes réservations', 'client.reservations', 'calendar-check'],
            ['Mes séjours', 'client.trips', 'map-pin'],
            ['Messages', 'client.inbox', 'inbox'],
            ['Favoris', 'client.favorites', 'heart'],
            ['Programme fidélité', 'client.programme', 'award'],
            ['Paiements', 'client.payments', 'credit-card'],
            ['Mes avoirs', 'client.credits', 'wallet'],
            ['Mes avis', 'client.reviews', 'star'],
            ['Documents', 'client.documents', 'file-text'],
            ['Notifications', 'client.notifications', 'bell'],
            ['Profil', 'client.profile', 'user'],
            ['Paramètres', 'client.settings', 'settings'],
            ['Aide', 'client.help', 'help-circle'],
        ];
    }

    /** Espace d'accueil selon le rôle. */
    public static function home(?User $user): string
    {
        return match (true) {
            !$user => route('home'),
            $user->isAdmin() || $user->hasRole('super_admin') || $user->hasRole('admin') => route('admin.dashboard'),
            $user->isHost() => route('host.dashboard'),
            default => route('client.dashboard'),
        };
    }
}
