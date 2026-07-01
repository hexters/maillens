# Changelog

All notable changes to `hexters/maillens` will be documented in this file.

## v1.0.0

Initial release.

- Capture outgoing mail by setting `MAIL_MAILER=lens` — messages are stored
  instead of delivered, with no external SMTP catcher required.
- Read captured mail in the browser at `/mail`: a light, two-pane inbox with
  HTML / Text / Source tabs, downloadable attachments, and a desktop / tablet /
  mobile preview switch.
- Almost no setup: the `lens` mailer is injected automatically, so you only set
  `MAIL_MAILER=lens` and run `php artisan migrate` once. The route and migration
  only load while `MAIL_MAILER=lens`, so the package stays out of the way
  everywhere else.
- Supports Laravel 10, 11, 12, and 13 on PHP 8.1+.
