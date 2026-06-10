<?php

namespace App\Support;

class YouTube
{
    /**
     * Extrai o ID de 11 caracteres de um link do YouTube em formatos comuns:
     * youtu.be/ID, youtube.com/watch?v=ID, youtube.com/embed/ID, shorts/ID,
     * com ou sem parâmetros extras. Retorna null se não encontrar.
     */
    public static function idFrom(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $patterns = [
            '#youtu\.be/([\w-]{11})#i',
            '#[?&]v=([\w-]{11})#i',
            '#/embed/([\w-]{11})#i',
            '#/shorts/([\w-]{11})#i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return $m[1];
            }
        }

        // Caso seja apenas o ID puro.
        if (preg_match('#^[\w-]{11}$#', trim($url))) {
            return trim($url);
        }

        return null;
    }
}
