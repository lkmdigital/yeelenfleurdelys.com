<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Installation initiale : rôles, moyens de paiement, puis les établissements du
 * groupe avec leurs comptes (administrateur + un gérant par établissement).
 *
 *   php artisan migrate --seed
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            PaymentMethodSeeder::class,
            EtablissementsSeeder::class,
        ]);
    }
}
