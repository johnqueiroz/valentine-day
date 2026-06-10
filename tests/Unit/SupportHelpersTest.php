<?php

namespace Tests\Unit;

use App\Support\MoonPhase;
use App\Support\YouTube;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class SupportHelpersTest extends TestCase
{
    public function test_youtube_id_extraction_from_various_formats(): void
    {
        $this->assertSame('dQw4w9WgXcQ', YouTube::idFrom('https://youtu.be/dQw4w9WgXcQ?t=5'));
        $this->assertSame('abcdEFGHijk', YouTube::idFrom('https://www.youtube.com/watch?v=abcdEFGHijk&list=x'));
        $this->assertSame('abcdEFGHijk', YouTube::idFrom('https://www.youtube.com/embed/abcdEFGHijk'));
        $this->assertSame('abcdEFGHijk', YouTube::idFrom('https://youtube.com/shorts/abcdEFGHijk'));
        $this->assertSame('abcdEFGHijk', YouTube::idFrom('abcdEFGHijk'));
        $this->assertNull(YouTube::idFrom('https://example.com/not-youtube'));
        $this->assertNull(YouTube::idFrom(null));
    }

    public function test_moon_phase_for_known_dates(): void
    {
        // Lua cheia em 16/02/2022; 14/02 é crescente gibosa (>90% iluminada).
        $valentine = MoonPhase::forDate(Carbon::create(2022, 2, 14));
        $this->assertSame('Crescente gibosa', $valentine['name']);
        $this->assertGreaterThan(0.85, $valentine['illumination']);

        // Lua nova de referência → iluminação próxima de 0.
        $newMoon = MoonPhase::forDate(Carbon::create(2000, 1, 6, 18, 14, 0, 'UTC'));
        $this->assertLessThan(0.05, $newMoon['illumination']);
    }
}
