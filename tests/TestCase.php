<?php

namespace Hexters\MailLens\Tests;

use Hexters\MailLens\MailLensServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [MailLensServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // MailLens turns on only when this matches config('maillens.mailer').
        $app['config']->set('mail.default', $this->mailer());
    }

    /**
     * The active mailer for the test app. Override to simulate MailLens being off.
     */
    protected function mailer(): string
    {
        return 'lens';
    }
}
