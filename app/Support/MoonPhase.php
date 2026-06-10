<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class MoonPhase
{
    /** Duração média do mês sinódico (lunação) em dias. */
    private const SYNODIC_MONTH = 29.53058867;

    /** Lua nova de referência: 2000-01-06 18:14 UTC. */
    private const REFERENCE_NEW_MOON = '2000-01-06 18:14:00';

    /**
     * Calcula a fase da lua para uma data.
     *
     * @return array{name: string, emoji: string, illumination: float, age_days: float}
     */
    public static function forDate(Carbon $date): array
    {
        $reference = Carbon::parse(self::REFERENCE_NEW_MOON, 'UTC');
        $daysSince = $reference->diffInSeconds($date->copy()->utc(), false) / 86400;

        // Idade da lua dentro da lunação atual (0 = nova).
        $age = fmod($daysSince, self::SYNODIC_MONTH);
        if ($age < 0) {
            $age += self::SYNODIC_MONTH;
        }

        $fraction = $age / self::SYNODIC_MONTH; // 0..1 ao longo da lunação
        $illumination = (1 - cos(2 * M_PI * $fraction)) / 2;

        [$name, $emoji] = self::label($fraction);

        return [
            'name' => $name,
            'emoji' => $emoji,
            'illumination' => round($illumination, 2),
            'age_days' => round($age, 1),
        ];
    }

    /**
     * Mapeia a fração da lunação (0..1) para uma das 8 fases.
     *
     * @return array{0: string, 1: string}
     */
    private static function label(float $fraction): array
    {
        // Cada fase ocupa 1/8 da lunação, centrada nos marcos.
        $index = (int) floor(($fraction * 8) + 0.5) % 8;

        return [
            0 => ['Lua nova', '🌑'],
            1 => ['Crescente côncava', '🌒'],
            2 => ['Quarto crescente', '🌓'],
            3 => ['Crescente gibosa', '🌔'],
            4 => ['Lua cheia', '🌕'],
            5 => ['Minguante gibosa', '🌖'],
            6 => ['Quarto minguante', '🌗'],
            7 => ['Minguante côncava', '🌘'],
        ][$index];
    }
}
