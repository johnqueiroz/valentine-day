<?php

namespace Tests\Feature;

use App\Models\Wrapped;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWrappedTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_wrapped_is_visible_with_spotify_payload(): void
    {
        $wrapped = Wrapped::create([
            'couple_name_1' => 'Ana',
            'couple_name_2' => 'Beto',
            'gifter_name' => 'Leonardo',
            'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
            'relationship_started_on' => '2022-02-14',
            'theme' => 'green',
            'published_at' => now(),
        ]);

        $this->get(route('wrapped.show', $wrapped))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Wrapped')
                ->where('wrapped.couple_name_1', 'Ana')
                ->where('wrapped.gifter_name', 'Leonardo')
                ->where('wrapped.youtube_id', 'dQw4w9WgXcQ')
                ->where('wrapped.moon.name', 'Crescente gibosa')
                ->has('wrapped.days_together')
            );
    }

    public function test_unpublished_wrapped_returns_404(): void
    {
        $wrapped = Wrapped::create([
            'couple_name_1' => 'Rascunho',
            'couple_name_2' => 'Teste',
            'theme' => 'blue',
            'published_at' => null,
        ]);

        $this->get(route('wrapped.show', $wrapped))->assertNotFound();
    }

    public function test_slug_is_generated_automatically(): void
    {
        $wrapped = Wrapped::create([
            'couple_name_1' => 'A',
            'couple_name_2' => 'B',
            'theme' => 'green',
        ]);

        $this->assertNotEmpty($wrapped->slug);
        $this->assertSame($wrapped->slug, $wrapped->fresh()->slug);
    }
}
