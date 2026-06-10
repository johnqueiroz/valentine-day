<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wrapped;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWrappedTest extends TestCase
{
    use RefreshDatabase;

    private function wrapped(): Wrapped
    {
        return Wrapped::create([
            'couple_name_1' => 'Ana',
            'couple_name_2' => 'Beto',
            'theme' => 'green',
        ]);
    }

    public function test_admin_can_open_edit_page_by_id(): void
    {
        $wrapped = $this->wrapped();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.wrappeds.edit', $wrapped->id))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Admin/Wrappeds/Edit'));
    }

    public function test_admin_can_update_wrapped(): void
    {
        $wrapped = $this->wrapped();

        $this->actingAs(User::factory()->create())
            ->put(route('admin.wrappeds.update', $wrapped->id), [
                'couple_name_1' => 'Ana Maria',
                'couple_name_2' => 'Beto',
                'youtube_url' => 'https://youtu.be/dQw4w9WgXcQ',
                'love_letter' => 'Te amo!',
                'theme' => 'blue',
                'published' => true,
                'slides' => [],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('wrappeds', [
            'id' => $wrapped->id,
            'couple_name_1' => 'Ana Maria',
            'love_letter' => 'Te amo!',
            'theme' => 'blue',
        ]);
    }

    public function test_guest_cannot_access_admin(): void
    {
        $this->get(route('admin.wrappeds.index'))->assertRedirect(route('login'));
    }

    public function test_public_route_still_resolves_by_slug(): void
    {
        $wrapped = Wrapped::create([
            'couple_name_1' => 'Ana',
            'couple_name_2' => 'Beto',
            'theme' => 'green',
            'published_at' => now(),
        ]);

        $this->get('/w/'.$wrapped->slug)->assertOk();
    }
}
