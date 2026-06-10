<?php

namespace Database\Seeders;

use App\Models\Wrapped;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class WrappedDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Evita duplicar a demo em re-seeds.
        Wrapped::where('slug', 'demo')->each(function (Wrapped $w) {
            Storage::disk('public')->deleteDirectory("wrappeds/{$w->id}");
            $w->delete();
        });

        $wrapped = Wrapped::create([
            'slug' => 'demo',
            'couple_name_1' => 'Ana',
            'couple_name_2' => 'Beto',
            'gifter_name' => 'Leonardo',
            'love_letter' => "Desde o dia em que te conheci, tudo ficou mais colorido.\n\nObrigado por cada risada, cada viagem e cada abraço. Esse presente é um pedacinho da nossa história. Eu te amo. 💚",
            'relationship_started_on' => Carbon::create(2022, 2, 14),
            'theme' => 'green',
            'published_at' => now(),
        ]);

        $slides = [
            ['type' => 'stat', 'title' => 'dias de nós dois', 'body' => 'Cada um deles valeu a pena.', 'meta' => ['value' => '847', 'unit' => 'dias']],
            ['type' => 'music', 'title' => 'Nossa música', 'body' => 'Tocou em todo lugar especial.', 'meta' => ['artist' => 'Djavan', 'plays' => '120']],
            ['type' => 'place', 'title' => 'Onde mais fomos felizes', 'body' => 'O nosso cantinho preferido.', 'meta' => ['location' => 'Praia do Rosa', 'count' => '5']],
            ['type' => 'milestone', 'title' => 'Quando tudo começou', 'body' => 'Um primeiro encontro inesquecível.', 'meta' => ['date' => '14/02/2022']],
            ['type' => 'message', 'title' => 'Eu te amo', 'body' => 'Obrigado por cada dia ao seu lado. Que venham muitos outros wraps juntos.', 'meta' => []],
        ];

        foreach ($slides as $i => $slide) {
            $wrapped->slides()->create([...$slide, 'position' => $i]);
        }

        // Gera fotos placeholder coloridas com GD (sem depender de internet).
        $colors = ['#ff5f8f', '#ffb86b', '#7a5cff', '#43e97b'];
        foreach ($colors as $i => $hex) {
            $path = "wrappeds/{$wrapped->id}/demo-{$i}.jpg";
            Storage::disk('public')->put($path, $this->solidJpeg($hex));

            $wrapped->photos()->create(['path' => $path, 'position' => $i]);
        }

        // Playlist do player: cada faixa com sua música, info e foto (cores distintas
        // para demonstrar a troca de cor de fundo ao mudar de faixa).
        $tracks = [
            ['title' => 'Still Loving You', 'artist' => 'Panda', 'youtube_url' => 'https://www.youtube.com/watch?v=jdYJf_ybyVo', 'hex' => '#1f6f4a'],
            ['title' => 'Nosso Talismã', 'artist' => 'Anavitória', 'youtube_url' => 'https://www.youtube.com/watch?v=2Vv-BfVoq4g', 'hex' => '#6a3093'],
            ['title' => 'Trevo (Tu)', 'artist' => 'Anavitória', 'youtube_url' => 'https://www.youtube.com/watch?v=k2qgadSvNyU', 'hex' => '#b3541e'],
        ];
        foreach ($tracks as $i => $t) {
            $photoPath = "wrappeds/{$wrapped->id}/tracks/track-{$i}.jpg";
            Storage::disk('public')->put($photoPath, $this->solidJpeg($t['hex']));

            $wrapped->tracks()->create([
                'title' => $t['title'],
                'artist' => $t['artist'],
                'youtube_url' => $t['youtube_url'],
                'photo_path' => $photoPath,
                'position' => $i,
            ]);
        }

        $this->command?->info("Wrapped de demonstração criado em /w/demo");
    }

    /**
     * Cria um JPEG 800x800 de cor sólida e retorna seus bytes.
     */
    protected function solidJpeg(string $hex): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');
        $im = imagecreatetruecolor(800, 800);
        imagefill($im, 0, 0, imagecolorallocate($im, $r, $g, $b));

        ob_start();
        imagejpeg($im, null, 85);
        $bytes = ob_get_clean();
        imagedestroy($im);

        return $bytes;
    }
}
