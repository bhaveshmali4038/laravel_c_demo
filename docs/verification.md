# Verification report — 30 October 2026

Environment: PHP 8.2.12, Laravel 12.70.2, Yajra DataTables 12.7.2, SQLite.

- `php artisan test`: 26 passed, 172 assertions. Tests use an in-memory SQLite database and fake upload storage.
- Authentication: registration, password hashing, email verification/resend, signed-link rejection, login/logout, remember tokens, login throttling, reset requests/tokens, password changes, duplicate normalized emails.
- Products: page rendering, create/read/update/delete, ownership checks, mass-assignment protection, required/custom/conditional/numeric/unique/file validation.
- Files: keep/replace/remove image, delete with product, cleanup after failed database save.
- DataTables: server-side pagination/search/numeric ordering/status filtering, per-owner isolation, invalid page size, escaped user text.
- `php artisan view:cache`: passed.
- `composer validate --no-check-publish`: valid.
- Composer dependency update: no security vulnerability advisories reported.
- Live browser: demo login, inventory JSON loading, name search, draft filter, second page, create form, local PNG preview loaded at 480 px.
- Responsive inspection: desktop at 1440×1000 and mobile at 390×844; no page-wide horizontal overflow on mobile. The table has its own horizontal scroll.
- Browser error/warning log: empty during the final check.
- Preview: docs/preview.png.

The temporary verification server used port 8001 because another project is using 8000.
To start the demo, double-click start-demo.bat or run `php artisan serve --host=127.0.0.1 --port=8001`.
Local email uses the log mailer; real SMTP delivery was not tested.
MySQL switching instructions are provided, but this installation and its tests use SQLite.
