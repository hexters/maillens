<?php

namespace Hexters\MailLens\Tests;

class DisabledTest extends TestCase
{
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
}
