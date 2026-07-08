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

        // Always register the migration so you can install then `php artisan migrate`
        // right away, before flipping MAIL_MAILER=lens.
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Config/config.php' => config_path('maillens.php'),
            ], 'maillens-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'maillens-migrations');
        }

        // The inbox itself is "on" only when the app is actually using the mailer.
        // MAIL_MAILER=lens ⇒ /mail routes load; anything else ⇒ they don't.
        if ($this->active()) {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        }
    }

    protected function active(): bool
    {
        return config('mail.default') === config('maillens.mailer', 'lens');
    }
}
