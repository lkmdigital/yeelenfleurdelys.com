<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\Response;

/** Résultat d'un appel InternalApi. */
class ApiResult
{
    private ?array $decoded = null;

    public function __construct(public readonly Response $response) {}

    public function status(): int
    {
        return $this->response->getStatusCode();
    }

    public function ok(): bool
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /** Corps JSON décodé, ou une clé (notation pointée). */
    public function json(?string $key = null, mixed $default = null): mixed
    {
        if ($this->decoded === null) {
            $content = $this->response->getContent();
            $this->decoded = is_string($content) ? (json_decode($content, true) ?? []) : [];
            if (!is_array($this->decoded)) {
                $this->decoded = ['value' => $this->decoded];
            }
        }
        return $key === null ? $this->decoded : data_get($this->decoded, $key, $default);
    }

    /** Liste « data » d'une réponse paginée ou simple tableau. */
    public function items(): array
    {
        $j = $this->json();
        if (isset($j['data']) && is_array($j['data']) && array_is_list($j['data'])) {
            return $j['data'];
        }
        return array_is_list($j) ? $j : [];
    }

    public function message(?string $fallback = null): string
    {
        return (string) ($this->json('message') ?: $this->json('error') ?: $fallback ?: 'Une erreur est survenue.');
    }

    /** Erreurs de validation au format Laravel (champ => [messages]). */
    public function errors(): array
    {
        $errors = $this->json('errors');
        if (is_array($errors) && $errors) {
            return $errors;
        }
        return $this->ok() ? [] : ['general' => [$this->message()]];
    }

    /** Redirige en arrière avec les erreurs si l'appel a échoué ; sinon null. */
    public function failBack(): ?\Illuminate\Http\RedirectResponse
    {
        if ($this->ok()) {
            return null;
        }
        if ($this->status() === 403) {
            return back()->with('error', $this->message("Vous n'avez pas accès à cette action."));
        }
        return back()->withErrors($this->errors())->withInput();
    }
}
