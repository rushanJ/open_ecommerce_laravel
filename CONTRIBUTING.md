# Contributing to open_ecommerce_laravel

Thank you for helping improve open_ecommerce_laravel. This document describes how we prefer contributions to be made.

## Branch naming

Use short, descriptive prefixes:

- `feature/short-description` — new functionality
- `fix/short-description` — bug fixes
- `docs/short-description` — documentation only
- `chore/short-description` — tooling, CI, formatting

## Commits

- Use the **imperative mood** in the subject line (e.g. “Add order export filter”).
- Keep the first line under **72 characters** when possible.
- Optional body: explain *why* for non-obvious changes.

## Pull requests

1. **Open an issue first** for large features (see Feature proposals below).
2. **Fork** the repository and create a branch from `main` (or the default branch).
3. Ensure **tests pass** and code style is applied (`composer pint` or `./vendor/bin/pint`).
4. In the PR description, include:
   - What changed and why
   - How to test manually (if applicable)
   - Screenshots for UI changes (optional)
5. Link related issues: `Fixes #123`.

PRs are reviewed for correctness, security implications, and maintainability.

## Code style (PHP)

- Follow **Laravel** conventions and PSR-12 where practical.
- Run **Laravel Pint** before pushing:

```bash
composer pint
# or
./vendor/bin/pint
```

## Tests

- Add or update **PHPUnit** tests for behavior changes when feasible.
- Run:

```bash
php artisan test
# or
composer test
```

Feature tests should avoid relying on slow external services unless mocked.

## Feature proposals

For significant work (new modules, payment providers, multi-tenant changes):

1. Open a **Feature request** issue describing goals, constraints, and alternatives.
2. Wait for maintainer feedback before investing large implementation time.

## License

By contributing, you agree that your contributions are licensed under the **MIT License** (see `LICENSE`).
