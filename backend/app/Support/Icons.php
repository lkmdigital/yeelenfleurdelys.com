<?php

namespace App\Support;

/** Icônes Lucide (resources/icons.php), chargées une seule fois par requête. */
class Icons
{
    private static ?array $icons = null;

    public static function inner(string $name): string
    {
        self::$icons ??= require resource_path('icons.php');
        return self::$icons[$name] ?? '';
    }
}
