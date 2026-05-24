# Security Policy

## Supported versions

| Version | Supported |
|---------|-----------|
| `0.1.x` (alpha) | Best-effort community support; production use at your own risk |

Security fixes are applied to the **default branch** first. Tag releases when possible so downstream can pin versions.

## Reporting a vulnerability

**Please do not** open a public GitHub issue for undisclosed security vulnerabilities.

Instead:

1. Email the maintainers at a **private** security contact (publish your project’s address in the repo **About** section or `README.md` once decided).
2. Include: description, affected component, reproduction steps, impact assessment, and suggested fix if any.
3. Allow a reasonable disclosure window (e.g. 90 days) before public disclosure, unless agreed otherwise.

We will acknowledge receipt and coordinate a fix and release.

## Security expectations

- open_ecommerce_laravel is **open-source alpha** software. You are responsible for **hosting hardening**, **TLS**, **secrets management**, and **compliance** (PCI, tax, consumer law) in your jurisdiction.
- **Never commit** real `.env` files, API keys, PayHere secrets, or database passwords to Git.
- Use **strong unique passwords** for admin accounts and database users.
- Keep **PHP, Laravel, and Composer dependencies** updated; run `composer audit` regularly.

## Secret handling

- Prefer **environment variables** or your host’s secret store for production.
- Rotate credentials after any suspected leak.
- Restrict file permissions on `storage/` and `bootstrap/cache/` (see `docs/DEPLOYMENT.md`).

## Payment callbacks (PayHere)

- PayHere and other gateways send **server-to-server** callbacks. Validate **signatures** and **merchant configuration**; never trust client-side payment state alone.
- Use **HTTPS** for notify URLs in production.
- Log and monitor failed signature or duplicate payment events.

## Admin access

- Change the **seeded default admin password** before exposing the site to the internet.
- Prefer **role-based** admin permissions; grant least privilege to each admin user.

## Uploads and media

- Admin uploads should stay behind **authentication** and **authorization** checks.
- Validate **MIME types** and **file size**; store uploads outside the web root except via `public/storage` symlink and controlled routes.
- Do not execute uploaded files as code.

## Disclosure

When a vulnerability is fixed in a public release, we will describe it in `CHANGELOG.md` without exploit details until users have had time to upgrade.
