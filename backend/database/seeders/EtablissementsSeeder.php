<?php

namespace Database\Seeders;

use App\Models\Accommodation;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Établissements du groupe Yeelen & Fleur de Lys.
 *
 * Organisation :
 *  - 1 compte administrateur (le propriétaire) : vue globale sur tous les établissements ;
 *  - 1 compte gérant par établissement : son tableau de bord ne montre que SON établissement
 *    (il peut ensuite inviter ses réceptionnistes depuis « Utilisateurs »).
 *
 * Données issues des pages Facebook du client. Les valeurs inconnues sont marquées
 * « À COMPLÉTER » et les chambres sans tarif sont créées INACTIVES (non réservables)
 * jusqu'à ce que le gérant saisisse le prix.
 *
 * Idempotent : peut être relancé sans créer de doublons.
 * Les mots de passe générés sont affichés UNE fois dans la console : à transmettre au client.
 */
class EtablissementsSeeder extends Seeder
{
    public function run(): void
    {
        $credentials = [];

        // ── Administrateur (propriétaire du groupe) ──────────────────────────
        $adminEmail = env('ADMIN_EMAIL', 'admin@yeelenfleurdelys.com');
        [$admin, $pwd] = $this->account($adminEmail, 'Administrateur Yeelen & Fleur de Lys', 'admin', null);
        $credentials[] = ['Administrateur (vue globale)', $adminEmail, $pwd];
        $this->attachRole($admin, 'admin');

        foreach ($this->establishments() as $e) {
            [$manager, $pwd] = $this->account($e['manager_email'], 'Gérant — ' . $e['name'], 'host', $e['phone']);
            $credentials[] = ['Gérant ' . $e['name'], $e['manager_email'], $pwd];
            $this->attachRole($manager, 'host');

            $accommodation = Accommodation::withoutGlobalScopes()->updateOrCreate(
                ['slug' => $e['slug']],
                [
                    'host_id' => $manager->id,
                    'name' => $e['name'],
                    'type' => $e['type'],
                    'description' => $e['description'],
                    'address' => $e['address'],
                    'city' => $e['city'],
                    'latitude' => $e['lat'],
                    'longitude' => $e['lng'],
                    'whatsapp' => $e['whatsapp'],
                    'price_per_night' => $e['from_price'],
                    'max_guests' => $e['max_guests'],
                    'bedrooms' => count($e['rooms']),
                    'bathrooms' => count($e['rooms']),
                    'amenities' => $e['amenities'],
                    'status' => 'published',
                    'check_in_time' => '14:00',
                    'check_out_time' => '12:00',
                    'cancellation_policy_hours' => 48,
                    'reception_24h' => true,
                ]
            );

            if (!$accommodation->establishment_code) {
                $accommodation->establishment_code = Accommodation::generateEstablishmentCode($e['city']);
                $accommodation->save();
            }

            foreach ($e['rooms'] as $r) {
                Room::updateOrCreate(
                    ['accommodation_id' => $accommodation->id, 'name' => $r['name']],
                    [
                        'type' => $r['type'],
                        'description' => $r['description'],
                        'capacity' => $r['capacity'],
                        'price_per_night' => $r['price'] ?? 0,
                        'amenities' => $r['amenities'],
                        'bedrooms' => 1,
                        'bathrooms' => 1,
                        'quantity' => $r['quantity'] ?? 1,
                        // Sans tarif connu : chambre masquée jusqu'à saisie du prix par le gérant.
                        'is_active' => isset($r['price']),
                    ]
                );
            }
        }

        if ($this->command && collect($credentials)->contains(fn ($c) => $c[2] !== null)) {
            $this->command->warn('Comptes créés — notez ces mots de passe (affichés une seule fois) :');
            $this->command->table(['Compte', 'E-mail', 'Mot de passe'], array_map(
                fn ($c) => [$c[0], $c[1], $c[2] ?? '(inchangé)'],
                $credentials
            ));
        }
    }

    /** Crée le compte s'il n'existe pas ; ne touche jamais au mot de passe d'un compte existant. */
    private function account(string $email, string $name, string $role, ?string $phone): array
    {
        $user = User::where('email', $email)->first();
        if ($user) {
            return [$user, null];
        }
        $password = Str::password(14, symbols: false);
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'phone' => $phone,
            'status' => 'active',
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return [$user, $password];
    }

    private function attachRole(User $user, string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }

    private function establishments(): array
    {
        $yeelenDescription = "Les Résidences Yeelen proposent des résidences meublées et un hôtel haut standing à Abidjan. "
            . "Appartements équipés et climatisés, idéals pour un court ou un long séjour, en famille ou pour le travail.";

        $yeelenRooms = [[
            'name' => 'Résidence meublée',
            'type' => 'apartment',
            'description' => 'Résidence meublée et climatisée. Tarif, nombre de pièces et équipements : À COMPLÉTER par le gérant.',
            'capacity' => 2,
            'price' => null, // À COMPLÉTER
            'amenities' => ['climatisation', 'wifi'],
        ]];

        return [
            [
                'slug' => 'hotel-fleur-de-lys-tiassale',
                'name' => 'Hôtel Fleur de Lys',
                'type' => 'hotel',
                'manager_email' => 'fleurdelys@yeelenfleurdelys.com',
                'phone' => '+225 07 03 24 44 64',
                'whatsapp' => '+2250703244464',
                'city' => 'Tiassalé',
                'address' => "Axe N'Douci, Tiassalé (à environ 1 h 30 d'Abidjan)",
                'lat' => 5.8986, // position approximative — à vérifier
                'lng' => -4.8233,
                'from_price' => 25000,
                'max_guests' => 2,
                'description' => "L'Hôtel Fleur de Lys vous accueille à Tiassalé, sur l'axe N'Douci, à environ 1 h 30 d'Abidjan. "
                    . "Chambres climatisées, piscine, restaurant et salles de réception pour vos événements. Ouvert tous les jours. "
                    . "Réservations : 07 03 24 44 64 / 05 44 54 66 86.",
                'amenities' => ['piscine', 'restaurant', 'climatisation', 'parking', 'Salles de réception'],
                'rooms' => [
                    [
                        'name' => 'Chambre simple',
                        'type' => 'single',
                        'description' => 'Chambre climatisée avec accès à la piscine.',
                        'capacity' => 2,
                        'price' => 25000,
                        'amenities' => ['climatisation', 'piscine'],
                    ],
                    [
                        'name' => 'Chambre avec balcon',
                        'type' => 'double',
                        'description' => 'Chambre climatisée avec balcon, petit-déjeuner inclus et accès à la piscine.',
                        'capacity' => 2,
                        'price' => 30000,
                        'amenities' => ['climatisation', 'balcon', 'petit-déjeuner', 'piscine'],
                    ],
                ],
            ],
            [
                'slug' => 'residences-yeelen-angre-mahou',
                'name' => 'Résidences Yeelen — Angré Mahou',
                'type' => 'apartment',
                'manager_email' => 'yeelen.angre@yeelenfleurdelys.com',
                'phone' => '+225 07 97 57 35 75',
                'whatsapp' => '+2250797573575',
                'city' => 'Abidjan',
                'address' => 'Cocody Angré, Mahou — Abidjan',
                'lat' => 5.3964, // position approximative — à vérifier
                'lng' => -3.9894,
                'from_price' => 0,
                'max_guests' => 2,
                'description' => $yeelenDescription . ' Site d\'Angré Mahou (Cocody). Contact : 07 97 57 35 75.',
                'amenities' => ['climatisation', 'wifi', 'parking'],
                'rooms' => $yeelenRooms,
            ],
            [
                'slug' => 'residences-yeelen-riviera-palmeraie',
                'name' => 'Résidences Yeelen — Riviera Palmeraie',
                'type' => 'apartment',
                'manager_email' => 'yeelen.palmeraie@yeelenfleurdelys.com',
                'phone' => '+225 07 48 46 42 94',
                'whatsapp' => '+2250748464294',
                'city' => 'Abidjan',
                'address' => 'Riviera Palmeraie, programme 3 — Cocody, Abidjan',
                'lat' => 5.3685, // position approximative — à vérifier
                'lng' => -3.9555,
                'from_price' => 0,
                'max_guests' => 2,
                'description' => $yeelenDescription . ' Site de Riviera Palmeraie, programme 3. Contact : 07 48 46 42 94.',
                'amenities' => ['climatisation', 'wifi', 'parking'],
                'rooms' => $yeelenRooms,
            ],
            [
                'slug' => 'residences-yeelen-koumassi-bia-sud',
                'name' => 'Résidences Yeelen — Koumassi Bia Sud',
                'type' => 'apartment',
                'manager_email' => 'yeelen.koumassi@yeelenfleurdelys.com',
                'phone' => null, // À COMPLÉTER
                'whatsapp' => null,
                'city' => 'Abidjan',
                'address' => 'Koumassi, Bia Sud — Abidjan',
                'lat' => 5.2925, // position approximative — à vérifier
                'lng' => -3.9493,
                'from_price' => 0,
                'max_guests' => 2,
                'description' => $yeelenDescription . ' Site de Koumassi Bia Sud.',
                'amenities' => ['climatisation', 'wifi', 'parking'],
                'rooms' => $yeelenRooms,
            ],
        ];
    }
}
