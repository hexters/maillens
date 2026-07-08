<p align="center">
  <img src="https://raw.githubusercontent.com/hexters/maillens/main/src/Resources/logo.png" alt="MailLens" width="120">
</p>

<h1 align="center">MailLens</h1>

<p align="center">
  <a href="https://packagist.org/packages/hexters/maillens"><img src="https://poser.pugx.org/hexters/maillens/v/stable" alt="Latest Stable Version"></a>
  <a href="https://packagist.org/packages/hexters/maillens"><img src="https://poser.pugx.org/hexters/maillens/downloads" alt="Total Downloads"></a>
  <a href="https://github.com/hexters/maillens/actions/workflows/tests.yml"><img src="https://github.com/hexters/maillens/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
  <a href="https://packagist.org/packages/hexters/maillens"><img src="https://poser.pugx.org/hexters/maillens/license" alt="License"></a>
</p>

MailLens catches the email your app sends while you are developing, so you can
read it in the browser instead of wiring up a real inbox. Set one env variable
and every message your Laravel app tries to send gets saved and shown at
`/mail`. There is nothing to install outside Composer, no Docker, and no SMTP
catcher running in the background.

If you have used Mailtrap or Mailpit before, it is the same idea, except it
lives inside your own app.

## Requirements

- PHP 8.1 or newer
- Laravel 10, 11, 12, or 13

## Install

```bash
composer require hexters/maillens --dev
```

Run the migration to create the table MailLens stores mail in:

```bash
php artisan migrate
```

Then point the mailer at MailLens in your `.env`:

```dotenv
MAIL_MAILER=lens
```

Now send mail the way you normally would and open `/mail` to read it. You do not
need to touch `config/mail.php`; MailLens adds the `lens` mailer for you.

## How it works

Setting `MAIL_MAILER=lens` sends your mail through a transport that stores the
message instead of delivering it. It keeps the subject, the sender and
recipients, the HTML and text bodies, any attachments, and the raw source. The
`/mail` page puts the message list on one side and the selected message on the
other, with tabs for HTML, plain text, and source, plus links to download any
attachments.

## Queued mail

MailLens only changes where your mail ends up, not how your queue works. If a
mailable is queued (`ShouldQueue`, `Mail::queue()`, or a queued notification),
it shows up in `/mail` after the queue runs the job, the same as it would before
a real send:

- `QUEUE_CONNECTION=database`: run a worker with `php artisan queue:work`
- `QUEUE_CONNECTION=redis`: run your worker, or Horizon if that is your setup
- `QUEUE_CONNECTION=sync`: nothing to run, the mail is captured right away

So if a queued email never shows up, check that a worker is running before you
blame MailLens.

## Turning it off

There is no separate switch. `MAIL_MAILER` is the switch. When it is set to
anything other than `lens`, MailLens stays quiet: it does not register the
`/mail` route and nothing routes through it. Keep `MAIL_MAILER=lens` out of your
production `.env` and your mail will send for real there.

## Password protection

By default `/mail` is open, which is fine on your own machine. If the inbox
lives somewhere other people can reach, such as a shared staging server, set a
password and MailLens asks for it before showing anything:

```dotenv
MAILLENS_PASSWORD=some-secret
```

Leave it unset and there is no lock screen.

## Configuration

Most people never need this, but you can publish the config file to change the
defaults:

```bash
php artisan vendor:publish --tag=maillens-config
```

```dotenv
MAILLENS_PASSWORD=            # protect /mail with a password (unset = open)
MAILLENS_ROUTE_PREFIX=mail    # the inbox lives here
MAILLENS_LIMIT=200            # how many messages to keep (null keeps all of them)
```

Captured mail is saved on your app's default database connection.

## Contributing

Bug reports and pull requests are welcome. Have a look at
[CONTRIBUTING.md](CONTRIBUTING.md) first.

## License

MIT. See [LICENSE](LICENSE).
