Testing helpers and cleanup

This project ships test helper routes under `routes/testing.php` that are intended for local development only. They are registered only when the application environment is `local`.

Security & Safety measures (applied):

- Cleanup endpoints are opt-in via an environment variable:

  ALLOW_TEST_CLEANUP=true

  If this is not set (or false), cleanup endpoints return 403 and do nothing.

- Cleanup operations are restricted to the test user `lrakthunder@gmail.com` and only delete records belonging to that user.

- Each cleanup endpoint is idempotent and will run at most once per test user. The server caches the "already run" state for 1 hour.

- The testing routes are guarded by a DB connectivity check and are only loaded in the `local` environment.

Recommended workflow

1. Use a dedicated test database for running Cypress. Update `cypress.config.js` to point at a separate DB to avoid any chance of touching production or development data.

2. Enable cleanup only in CI or a disposable local environment by setting `ALLOW_TEST_CLEANUP=true` in your `.env` on that machine.

3. Prefer passing the created resource id to cleanup endpoints (future improvement). This makes cleanup explicit and minimizes accidental deletions.

4. Do not push a `.env` with `ALLOW_TEST_CLEANUP=true` to shared repos. Keep it strictly per-environment.

5. If you need to restore data after accidental deletion, restore from your DB backups or use MySQL binlogs if available.

If you want, I can:
- Update Cypress tests to explicitly pass created resource ids to cleanup endpoints.
- Add a `php artisan test:cleanup` command to run cleanup locally with an extra confirmation prompt.
