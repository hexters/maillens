<?php

namespace Hexters\MailLens\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class DisabledTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Simulate an app whose MAIL_MAILER is NOT "lens".
     */
    protected function mailer(): string
    {
        return 'log';
    }

    public function test_inbox_route_is_not_registered_when_mailer_is_not_lens(): void
    {
        $this->get('/mail')->assertNotFound();
    }

    public function test_the_table_migrates_even_when_the_mailer_is_not_lens(): void
    {
        // RefreshDatabase ran the package migration despite MAIL_MAILER=log,
        // proving you can migrate right after install, before flipping to lens.
        $this->assertTrue(Schema::hasTable('maillens_messages'));
    }
}
