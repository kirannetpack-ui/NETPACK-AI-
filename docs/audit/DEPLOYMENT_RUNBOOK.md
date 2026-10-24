# NETPACK deployment runbook

Use with RELEASE_REVIEW.md. This is a release procedure, not a declaration that acceptance is complete.

## Build

Use PHP 8.3+, required Composer extensions, MySQL 8 and Node 24. Install committed lockfiles and run:

```sh
composer install --no-interaction --prefer-dist
npm ci
composer validate --strict
composer audit --locked
npm audit --omit=dev
php artisan test
npm run test:pwa
npm run build
```

Build in CI. Serve only public/ as the web root; never expose the repository, environment files, database files or private upload directories. Production needs Composer runtime dependencies, not the Node build-tool tree.

## Configure

Start from .env.production.example. Supply a unique APP_KEY, actual database credentials, HTTPS origin, secure cookies, mail, private storage and integration secrets through the target's secret manager. Do not copy the local environment or demo credentials. Keep live payments off until merchant sandbox acceptance.

Persistent private local storage is possible on a durable server with tested access controls/backups. Ephemeral hosting requires durable private object storage. Shared session/cache stores must be accessible to all web and worker instances. Apply migrations for session/cache/job tables.

## Release order

1. Back up database/uploads and test restoration. Record application revision and lockfiles.
2. Pause writes/workers for incompatible schema/storage changes.
3. Install production dependencies with `composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction`.
4. Run `php artisan migrate --force`. Reconcile legacy assignment ownership and private documents using RELEASE_REVIEW.md.
5. Run `php artisan app:production-check` on the target. A pass covers selected configuration; it does not replace acceptance.
6. Run `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`.
7. Restart persistent workers using `php artisan queue:restart`. Run supervised `php artisan queue:work --tries=3 --timeout=90`. Schedule one `php artisan schedule:run` per minute and inspect schedule:list.
8. Verify health/readiness, role login, shipment workflows, private documents, provider sandbox callbacks, handover codes and COD reconciliation. Test PWA installation from HTTPS on supported devices.
9. Reopen traffic after acceptance. Monitor failed jobs, exceptions, callback failures, stale shipments and reconciliation totals.

## Recovery

Keep the previous build and tested database/storage snapshot. Prefer a compatible previous application over destructive schema rollback. New ownership/status migrations prevent unsafe narrowing when newer records exist. Never reset an operational database with development reset commands.

For corruption or reconciliation failures, pause writes, preserve evidence, restore the reviewed snapshot in isolation, compare custody/transaction records, then agree the recovery cutover with operations. Confirm both database and private upload evidence before reopening.

## Local preview

This audit's isolated preview uses MySQL at 127.0.0.1:3307 with database netpack_audit, and web port 8088. Tests use netpack_audit_tests. These are development services, not an internet deployment.
