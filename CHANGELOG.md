# Changelog

All notable changes to `hexters/maillens` will be documented in this file.

## v1.3.2

- The migration is always registered, so you can run `php artisan migrate` right
  after install, before setting `MAIL_MAILER=lens`.

## v1.3.1

- Fix horizontal scroll from long subjects: the list truncates them to one
  line, while the reading pane wraps the subject in full and, on mobile, moves
  the date up into the app bar.

## v1.3.0

- Redesign the inbox around the sidebar: the branding and the Lock button move
  to the top of the sidebar and the top navbar is gone.
- New sidebar toolbar with a search box and icon buttons (with tooltips) for
  Mark all as read, Refresh, and Clear all messages.
- Search the inbox by subject, sender, recipient, and text body; the query is
  kept in the URL so it survives auto-refresh and message selection.
- Clear all now asks for confirmation in a modal instead of the browser popup.
- Simpler list rows (subject over `to: <addr>`, relative time) and the message
  date moved to the top-right of the reading pane.

## v1.2.0

- Optional password protection: set `MAILLENS_PASSWORD` and `/mail` shows a lock
  screen until the password is entered. Left unset, the inbox stays open. Adds a
  Lock button to leave the session.

## v1.1.2

- Links and buttons inside the email preview now open in a new tab instead of
  navigating inside the preview frame.

## v1.1.1

- Fix the GitHub Actions test matrix so dependencies resolve on every supported
  PHP and Laravel version.

## v1.1.0

- Public message URLs use a per-message UUID instead of the numeric id, so row
  ids and message counts are not exposed.
- The inbox refreshes itself like Mailtrap: it polls while visible and refreshes
  on focus, reloading only when mail actually changed.
- Mobile-friendly, one-pane-at-a-time layout with a Gmail-style message view.
- Send `no-store` on the inbox so browsers never show a stale copy.

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
