<?php

namespace Hexters\MailLens\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;

class LockTest extends TestCase
{
    use RefreshDatabase;

    public function test_inbox_is_open_when_no_password_is_set(): void
    {
        config(['maillens.password' => null]);

        $this->get('/mail')->assertOk();
    }

    public function test_inbox_is_locked_when_a_password_is_set(): void
    {
        config(['maillens.password' => 'secret']);

        $this->get('/mail')
            ->assertStatus(401)
            ->assertSee('password protected');
    }

    public function test_correct_password_unlocks_the_inbox(): void
    {
        config(['maillens.password' => 'secret']);

        $this->post('/mail/unlock', ['password' => 'secret'])
            ->assertRedirect(route('maillens.index'))
            ->assertSessionHas('maillens_unlocked', true);
    }

    public function test_wrong_password_is_rejected(): void
    {
        config(['maillens.password' => 'secret']);

        $this->post('/mail/unlock', ['password' => 'nope'])
            ->assertStatus(401)
            ->assertSee('Wrong password');
    }

    public function test_an_unlocked_session_can_view_the_inbox(): void
    {
        config(['maillens.password' => 'secret']);

        $this->withSession(['maillens_unlocked' => true])
            ->get('/mail')
            ->assertOk();
    }

    public function test_the_logo_stays_public_so_the_lock_screen_can_show_it(): void
    {
        config(['maillens.password' => 'secret']);

        $this->get('/mail/logo.png')
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_logout_locks_the_inbox_again(): void
    {
        config(['maillens.password' => 'secret']);

        $this->withSession(['maillens_unlocked' => true])
            ->post('/mail/logout')
            ->assertRedirect(route('maillens.index'))
            ->assertSessionMissing('maillens_unlocked');
    }

    public function test_logout_button_shows_only_when_a_password_is_set(): void
    {
        config(['maillens.password' => 'secret']);
        $this->withSession(['maillens_unlocked' => true])
            ->get('/mail')
            ->assertOk()
            ->assertSee(route('maillens.logout'));

        config(['maillens.password' => null]);
        $this->get('/mail')
            ->assertOk()
            ->assertDontSee(route('maillens.logout'));
    }
}
