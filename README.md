# MailLens

Catch outgoing mail during local development and read it in the browser — no
external SMTP catcher, no Docker, nothing to run. Point Laravel at MailLens and
every email your app *would* send is stored instead of delivered, ready to read
at `/mail`.

Think Mailtrap / Mailpit / MailCatcher, but as a Composer package that lives
inside your app.

## Requirements

- PHP 8.1+
- Laravel 10, 11, 12, or 13

## Install

```bash
composer require hexters/maillens --dev
```

The service provider is auto-discovered and the messages table migrates
automatically. Then flip one switch in `.env`:

```dotenv
MAIL_MAILER=lens
```

That's it. Send mail as usual and open **`/mail`**.

> MailLens injects the `lens` mailer for you — you do **not** need to touch
> `config/mail.php`.

## How it works

`MAIL_MAILER=lens` routes mail through a custom Symfony transport that never
delivers. Instead it parses each message (subject, from/to/cc, HTML + text
bodies, attachments, raw source) and stores it. The `/mail` inbox is a
self-contained two-pane UI — message list on the left, preview on the right,
with HTML / Text / Source tabs and downloadable attachments.

## Safety

There is no separate on/off flag — `MAIL_MAILER` *is* the switch. When it isn't
`lens`, MailLens loads nothing: no `/mail` route, no migration, no transport in
use. Keep `MAIL_MAILER=lens` out of your production `.env` and mail flows
normally there.

## Configuration (optional)

```bash
php artisan vendor:publish --tag=maillens-config
```

```dotenv
MAILLENS_ROUTE_PREFIX=mail  # inbox lives at /mail
MAILLENS_LIMIT=200          # keep the last N messages (null = keep all)
```

Captured mail is stored on your app's **default database connection**.

## License

MIT © Asep SS
