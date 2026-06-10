<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class DominantColor
{
    /**
     * Cor média/dominante de uma imagem no disco "public", em hex (#rrggbb).
     * Usada para tingir o fundo do player no estilo Spotify. Retorna null se
     * não conseguir ler a imagem.
     */
    public static function of(?string $path): ?string
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $bytes = Storage::disk('public')->get($path);
        $src = @imagecreatefromstring($bytes);
        if ($src === false) {
            return null;
        }

        // Reduz para 1x1 — a interpolação faz a média de todos os pixels.
        $tiny = imagecreatetruecolor(1, 1);
        imagecopyresampled($tiny, $src, 0, 0, 0, 0, 1, 1, imagesx($src), imagesy($src));
        $rgb = imagecolorat($tiny, 0, 0);

        imagedestroy($src);
        imagedestroy($tiny);

        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
