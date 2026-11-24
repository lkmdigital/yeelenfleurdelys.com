<?php

namespace App\Support;

/**
 * Services proposés par un établissement EN PLUS des chambres (salles, restaurant, piscine,
 * long séjour…). Ils ne se réservent pas en ligne : le client demande un devis sur WhatsApp.
 * Déduits des équipements saisis par le gérant (et du type d'établissement).
 */
class EstablishmentServices
{
    /** équipement (minuscules, sans accents) => [icône, titre, phrase] */
    private const BY_AMENITY = [
        'salles de reception' => ['sparkles', 'Salles de réception', 'Mariages, séminaires, anniversaires : nous préparons votre événement.'],
        'salle de reception' => ['sparkles', 'Salles de réception', 'Mariages, séminaires, anniversaires : nous préparons votre événement.'],
        'salle de conference' => ['sparkles', 'Salle de conférence', 'Réunions et séminaires, avec ou sans hébergement.'],
        'restaurant' => ['utensils', 'Restaurant', 'Repas sur place, repas de groupe ou plateau pour vos invités.'],
        'piscine' => ['waves', 'Piscine', 'Renseignez-vous sur l’accès à la piscine et les formules journée.'],
        'navette' => ['car', 'Navette', 'Transfert depuis Abidjan ou la gare, sur demande.'],
    ];

    /** @return list<array{icon:string,title:string,text:string,message:string}> */
    public static function for(array $acc): array
    {
        $out = [];
        foreach ((array) ($acc['amenities'] ?? []) as $a) {
            $key = self::normalize((string) $a);
            if (isset(self::BY_AMENITY[$key])) {
                $out[self::BY_AMENITY[$key][1]] = self::BY_AMENITY[$key];
            }
        }
        if (!empty($acc['shuttle_service'])) {
            $out['Navette'] = self::BY_AMENITY['navette'];
        }
        // Résidences meublées : les séjours à la semaine ou au mois se négocient.
        if (in_array($acc['type'] ?? null, ['furnished_residence', 'residence', 'apartment', 'residence_meublee'], true)
            || str_contains(mb_strtolower($acc['name'] ?? ''), 'résidence')) {
            $out['Long séjour'] = ['calendar-days', 'Long séjour', 'À la semaine ou au mois : tarif adapté à la durée de votre séjour.'];
        }

        return array_values(array_map(fn ($s) => [
            'icon' => $s[0],
            'title' => $s[1],
            'text' => $s[2],
            'message' => "Bonjour, je souhaite un devis pour : {$s[1]} — " . ($acc['name'] ?? '') . '.',
        ], $out));
    }

    private static function normalize(string $s): string
    {
        $s = mb_strtolower(trim($s));
        return strtr($s, ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'à' => 'a', 'ç' => 'c', '_' => ' ']);
    }
}
