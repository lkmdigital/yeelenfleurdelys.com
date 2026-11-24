<?php

namespace App\Support;

/**
 * Photos d'illustration PROVISOIRES (Unsplash, licence libre), affichées tant qu'un
 * établissement ou une chambre n'a pas de photo. Elles disparaissent d'elles-mêmes dès que
 * le gérant ajoute ses photos. Pour les retirer complètement : supprimer ce fichier et ses appels
 * (establishment-card, room-card, gallery, pages établissement et chambre).
 */
class DemoPhotos
{
    /** Galerie par établissement : la première photo sert aussi de couverture. */
    private const GALLERIES = [
        'hotel-fleur-de-lys-tiassale' => [
            'photo-1594433575301-cf59b8ada6b1', // piscine et bâtiment
            'photo-1582719508461-905c673771fd', // transats au bord de la piscine
            'photo-1631049307264-da0ec9d70304', // chambre
            'photo-1618773928121-c32242e63f39', // lit
            'photo-1584622650111-993a426fbf0a', // salle de bain
        ],
        'residences-yeelen-angre-mahou' => [
            'photo-1628592102751-ba83b0314276', // salon
            'photo-1630699375019-c334927264df', // chambre
            'photo-1484154218962-a197022b5858', // cuisine
            'photo-1633104069776-ea7e61258ec9', // salle de bain
        ],
        'residences-yeelen-riviera-palmeraie' => [
            'photo-1663756915301-2ba688e078cf', // salon
            'photo-1612320743558-020669ff20e8', // chambre
            'photo-1630699376167-3870469e7598', // cuisine
            'photo-1564540583246-934409427776', // salle de bain
        ],
        'residences-yeelen-koumassi-bia-sud' => [
            'photo-1738168246881-40f35f8aba0a', // salon
            'photo-1612320583049-eabe3c21bd94', // chambre
            'photo-1629042306650-62a83c847b15', // cuisine
            'photo-1633104069776-ea7e61258ec9', // salle de bain
        ],
    ];

    /** Photos de chambres, pour les établissements qui n'ont pas de galerie dédiée. */
    private const ROOMS = [
        'photo-1631049307264-da0ec9d70304',
        'photo-1618773928121-c32242e63f39',
        'photo-1630699375019-c334927264df',
        'photo-1612320583049-eabe3c21bd94',
        'photo-1612320743558-020669ff20e8',
    ];

    private static function url(string $id, int $width): string
    {
        return "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$width}&q=70";
    }

    private static function ids(array $acc): array
    {
        return self::GALLERIES[$acc['slug'] ?? ''] ?? self::ROOMS;
    }

    /** Couverture d'un établissement (cartes). */
    public static function for(array $acc, int $width = 800): string
    {
        return self::url(self::ids($acc)[0], $width);
    }

    /** Galerie complète d'un établissement (page établissement). */
    public static function gallery(array $acc): array
    {
        return array_map(fn ($id) => self::url($id, 1600), self::ids($acc));
    }

    /**
     * Photos d'une chambre : toujours une photo de chambre en premier (qui varie d'une chambre
     * à l'autre quand l'établissement en a plusieurs), puis le reste de la galerie, sans le salon
     * ou la façade de couverture.
     */
    public static function room(array $acc, array $room, int $width = 1600): array
    {
        $gallery = array_slice(self::ids($acc), 1) ?: self::ROOMS;
        $bedrooms = array_values(array_intersect($gallery, self::ROOMS)) ?: [self::ROOMS[0]];
        $first = $bedrooms[((int) ($room['id'] ?? 0)) % count($bedrooms)];
        $ids = array_values(array_unique(array_merge([$first], $gallery)));

        return array_map(fn ($id) => self::url($id, $width), $ids);
    }
}
