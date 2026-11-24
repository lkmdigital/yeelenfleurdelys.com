<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Support\ApiResult;

/** Base de l'administration globale (tous les établissements). */
abstract class AdminController extends Controller
{
    /** Pagination des réponses admin : { data, pagination } ou paginateur Laravel. */
    protected function meta(ApiResult $res): array
    {
        return $res->json('pagination') ?? $res->json();
    }

    /** Ne garde que les valeurs renseignées d'un filtre de liste. */
    protected function only(array $values): array
    {
        return array_filter($values, fn ($v) => $v !== null && $v !== '' && $v !== 'all');
    }
}
