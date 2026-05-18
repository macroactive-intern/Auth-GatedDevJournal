<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_the_journal_page(): void
    {
        $this->get('/journal')->assertRedirect('/login');
    }

    public function test_authenticated_users_can_view_the_journal_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/journal')
            ->assertOk()
            ->assertSee('Dev Journal');
    }
}
