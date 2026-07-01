# Contributing

Thanks for taking the time to help out. MailLens is open to bug reports and pull
requests, and small fixes are just as welcome as big features.

## Reporting a bug

Open an issue and include enough for someone to reproduce it: your PHP and
Laravel versions, what you did, what you expected, and what happened instead. A
short code sample or a failing test says more than a paragraph of description.

## Working on the code

1. Fork the repo and create a branch off `main`.
2. Install the dev dependencies:

   ```bash
   composer install
   ```

3. Make your change. Try to keep the style consistent with the code around it.
4. Run the tests before you open a pull request:

   ```bash
   composer test
   ```

If you are adding behavior, add a test for it. If you are fixing a bug, a test
that fails without your fix and passes with it is the best way to show the bug
is real and stays fixed.

## Pull requests

Keep each pull request focused on one thing. Explain what it does and why in the
description. If it changes how the package is used, update the README in the same
pull request so the docs do not fall behind.

By contributing, you agree that your work is released under the MIT license,
same as the rest of the project.
