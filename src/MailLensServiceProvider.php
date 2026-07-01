<?php

namespace Hexters\MailLens;

use Hexters\MailLens\Transport\MailLensTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class MailLensServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/config.php', 'maillens');

        // Make MAIL_MAILER=lens a valid mailer without the host app touching
        // config/mail.php. It simply points at our capturing transport.
        config([
            'mail.mailers.' . config('maillens.mailer', 'lens') => ['transport' => 'maillens'],
        ]);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/Resources/views', 'maillens');

        // Register the capturing transport so the "lens" mailer can be built.
        Mail::extend('maillens', fn () => new MailLensTransport);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Config/config.php' => config_path('maillens.php'),
            ], 'maillens-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'maillens-migrations');
        }

        // MailLens is "on" only when the app is actually using its mailer.
        // MAIL_MAILER=lens ⇒ inbox routes + table load; anything else ⇒ nothing.
        if ($this->active()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }
    }

    protected function active(): bool
    {
        return config('mail.default') === config('maillens.mailer', 'lens');
    }
}
