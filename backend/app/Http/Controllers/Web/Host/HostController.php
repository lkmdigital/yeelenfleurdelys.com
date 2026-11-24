<?php

namespace App\Http\Controllers\Web\Host;

use App\Http\Controllers\Controller;
use App\Support\InternalApi;
use Illuminate\Support\Facades\View;

/**
 * Base de l'espace gérant : 1 compte gérant = 1 établissement.
 * L'établissement est le premier renvoyé par /api/accommodations/my (le collaborateur
 * voit celui de son propriétaire). Son nom est partagé avec le gabarit (barre haute).
 */
abstract class HostController extends Controller
{
    private ?array $property = null;

    protected function property(): array
    {
        if ($this->property === null) {
            $res = InternalApi::get('accommodations/my');
            $list = $res->ok() ? $res->items() : [];
            $this->property = $list[0] ?? [];
            View::share('hostProperty', $this->property);
        }
        abort_if(!$this->property, 404, "Aucun établissement n'est rattaché à ce compte.");

        return $this->property;
    }

    protected function propertyId(): int
    {
        return (int) $this->property()['id'];
    }

    /** Vue de l'espace gérant (l'établissement est toujours chargé, pour la barre haute). */
    protected function page(string $view, array $data = [])
    {
        $this->property();
        return view("web.host.$view", $data);
    }
}
