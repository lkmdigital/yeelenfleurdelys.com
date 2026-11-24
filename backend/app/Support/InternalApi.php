<?php

namespace App\Support;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Symfony\Component\HttpFoundation\Response;

/**
 * Appelle un endpoint de l'API (routes/api.php) depuis une page web Blade, sans requête HTTP.
 *
 * Les pages Blade réutilisent ainsi exactement la même logique métier que l'API
 * (tarifs, garanties, disponibilités, paiements, permissions des gérants…), déjà
 * couverte par les tests. L'utilisateur connecté en session est reconnu par l'API :
 * Sanctum vérifie d'abord la garde « web » (config/sanctum.php → guard).
 *
 *   $res = InternalApi::get('accommodations/1');
 *   $res->ok();  $res->json('name');  $res->errors();
 */
class InternalApi
{
    public static function get(string $uri, array $query = []): ApiResult
    {
        return static::call('GET', $uri, $query);
    }

    public static function post(string $uri, array $data = [], array $files = []): ApiResult
    {
        return static::call('POST', $uri, $data, $files);
    }

    public static function put(string $uri, array $data = []): ApiResult
    {
        return static::call('PUT', $uri, $data);
    }

    public static function patch(string $uri, array $data = []): ApiResult
    {
        return static::call('PATCH', $uri, $data);
    }

    public static function delete(string $uri, array $data = []): ApiResult
    {
        return static::call('DELETE', $uri, $data);
    }

    public static function call(string $method, string $uri, array $data = [], array $files = []): ApiResult
    {
        $outer = app('request');
        $path = '/api/' . ltrim($uri, '/');

        $server = $outer->server->all();
        // Sans Referer/Origin, Sanctum ne relance pas de session « stateful » dans la sous-requête :
        // c'est la session de la page (déjà démarrée) qui sert, partagée explicitement ci-dessous.
        unset($server['CONTENT_TYPE'], $server['HTTP_CONTENT_TYPE'], $server['CONTENT_LENGTH'],
            $server['HTTP_REFERER'], $server['HTTP_ORIGIN'], $server['HTTP_AUTHORIZATION']);

        $sub = Request::create($path, strtoupper($method), static::clean($data), $outer->cookies->all(), $files, $server);
        $sub->headers->set('Accept', 'application/json');
        $sub->headers->set('X-Internal-Web', '1');
        $sub->setUserResolver($outer->getUserResolver());
        if ($outer->hasSession()) {
            $sub->setLaravelSession($outer->session());
        }

        app()->instance('request', $sub);
        Facade::clearResolvedInstance('request');

        try {
            $response = app('router')->dispatch($sub);
        } catch (\Throwable $e) {
            $response = app(ExceptionHandler::class)->render($sub, $e);
        } finally {
            app()->instance('request', $outer);
            Facade::clearResolvedInstance('request');
        }

        return new ApiResult($response);
    }

    /** Les booléens deviennent 1/0 (règles « boolean » de Laravel) ; les null sont conservés. */
    private static function clean(array $data): array
    {
        array_walk_recursive($data, function (&$v) {
            if ($v === true) $v = 1;
            elseif ($v === false) $v = 0;
        });
        return $data;
    }
}
