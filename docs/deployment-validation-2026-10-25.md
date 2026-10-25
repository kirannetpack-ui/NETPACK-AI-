# Deployment repair and validation — 25 October 2026

Target: https://netpack-ai.vercel.app (demo environment).

## Repairs

- Run the PHP function in Singapore beside the Neon database. Use the unpooled PostgreSQL URL for PDO session and transaction compatibility; retain managed credentials in Vercel.
- Execute queued work synchronously because this deployment has no persistent queue worker. Allow up to 60 seconds per invocation.
- Authenticate native API clients without requiring a browser session; keep account approval and temporary-password checks.
- Replace MySQL-only revenue date queries with PostgreSQL-compatible expressions.
- Register the PWA from the shared JavaScript entry point and expose installation metadata on public, authentication, and role layouts.
- Refresh service workers without the HTTP cache, remove obsolete application caches, and fetch the manifest from the network. Continue excluding authenticated operational data from offline storage.
- Replace mislabeled JPEG installation icons with genuine 180, 192, and 512 pixel PNGs. The deterministic icon source is `scripts/generate-pwa-icons.ps1`.
- Point the Capacitor configuration at the deployed Vercel origin and use the correct company name.

## Verification

- Ten seeded roles: login reaches the appropriate dashboard with HTTP 200; a subsequent authenticated page request retains the session. Staff with all-service scope correctly use the administrator dashboard.
- API: login 200, authenticated profile 200, logout 200, revoked token 401.
- Health and database/cache readiness: HTTP 200.
- Actual browser: super-administrator form login reaches the operational dashboard.
- Automated checks: authentication and role-sidebar feature tests; health/configuration checks; offline/cache isolation, install prompt handling, manifest icon encoding and sizes.

## Native delivery boundary

This repository contains Capacitor configuration but no checked-in Android/iOS native projects or distributable binaries. The configuration and API are corrected; an already-installed native application must be rebuilt and redistributed to pick up the new server URL. Device installation and app-store distribution were not tested here.

This deployment continues to expose demo accounts for workflow review. It is not designated for live customer or financial records.
