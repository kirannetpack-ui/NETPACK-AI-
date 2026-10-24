# NETPACK initial repository audit

Date: 23 October 2026 (Asia/Katmandu)

Historical discovery snapshot. See [RELEASE_REVIEW.md](RELEASE_REVIEW.md) for subsequent repairs, current verification and remaining release blockers.

## Scope and current conclusion

This is the first discovery and repair stage, not a completed certification. The source inventory contains 836 files, 2,281 named PHP function declarations, 135 controllers, 80 models, 132 migrations and 585 registered routes. Generated build assets and vendor dependencies are excluded from the source-file inventory. Named declarations are a navigation aid: closures, JavaScript handlers, templates and configuration still require review. Inventory entries marked Unreviewed have not been behaviorally audited.

Baseline: `php artisan test --compact` passed 259 tests / 2,050 assertions in 88.74 seconds. `npm run build` succeeded. These results describe local SQLite automated tests and a frontend asset build, not live carrier integrations, MySQL compatibility, mobile builds or complete business acceptance.

## Architecture observed

- PHP 8.3 / Laravel 12 monolith, Blade templates, Tailwind, Alpine and Vite.
- Multiple shipment representations: Shipment, DomesticShipment, InternationalShipment, Order, EcommerceOrder, ShipmentAssignment and ManifestShipment. Cross-model consistency needs explicit tracing.
- Session-authenticated role portals and a limited Sanctum API. Existing customer/rider API controller files are not proof of registered routes; the API route inventory is authoritative.
- Domestic partner routing, rates, pickup requests, manifest bags, scans and rider handover components exist.
- International rate calculations, feeder charges, hubs/agencies, MAWBs, customs documents, carrier gateway and tracking subscriptions exist.
- E-commerce direct and three-leg assignments, rider offers, OTPs, COD and earnings ledgers exist.
- PWA manifest, service worker and offline page exist. Background shipment synchronization is a console-log placeholder.
- Capacitor configuration exists, pointing to a hosted website. package.json has no Capacitor runtime or platform dependencies, and no Android/iOS platform source directories were found in the root. A working native application has not been established.
- CI defines SQLite tests, a MySQL schema/seed check and frontend build. CI uses Node 20 while package.json requests Node 24; README also specifies Node 20+. Align and verify this separately.

## Initial issue register

| ID | Priority | Evidence | Finding / operational impact | State |
|---|---|---|---|---|
| TRACK-01 | Critical | app/Services/Tracking/Drivers/SmartAutonomousDriver.php::track | Elapsed time generated purported carrier scans, delivery and consignee signature without evidence; sync persisted them. | Fixed: unavailable result preserves last confirmed data. |
| TRACK-02 | Critical | app/Services/Tracking/MawbFlightTrackingService.php::syncMawb | Schedule age invented departure, customs clearance and release; landing also implied cleared cargo. | Fixed: no elapsed-time progression; landing cannot clear cargo. Verified airborne telemetry retains departure progression without regressing cleared states. |
| AUTH-01 | Critical | routes/api.php carrier webhook; app/Http/Controllers/Api/CarrierWebhookController.php::handle | Public webhook has no authentication/signature check before updating shipments. Existing tests explicitly submit unsigned payloads. | Open: add provider-specific authentication, replay protection, carrier-to-shipment binding and rejection tests. |
| AUTH-02 | Critical | app/Http/Controllers/Seller/EcommerceBookingController.php::show | Assignment is selected by arbitrary ID without seller ownership check. Index uses names/phone rather than immutable ownership. | Open: add seller ownership key, safe legacy backfill, policies and cross-seller tests. |
| PRIV-01 | High | app/Http/Controllers/Auth/RegistrationController.php::register; app/Http/Controllers/Rider/SettingsController.php::updateProfile | Identity and vehicle documents stored on public disk. KYCController uses private disk, so paths are inconsistent. | Open: private storage, authorized delivery, existing-file migration and access tests. |
| PWA-01 | High | public/service-worker.js fetch handler | All other successful same-origin GET responses can enter static cache; authenticated JSON/documents outside listed prefixes need exclusion. | Open: explicit public-static allowlist, cache cleanup and account-switch tests. |
| PWA-02 | High | public/service-worker.js sync handler | Claimed offline booking synchronization only logs text; no persisted queue or retry contract. | Open: design queue with idempotency/conflicts, or clearly limit offline support. |
| ECOM-01 | High | app/Services/EcommerceDispatchService.php::acceptJob / directAssignRider | Read-then-write assignment and counter updates lack transaction/locking; repeat acceptance increments active capacity again. | Open: atomic eligibility, capacity/COD reservation and replay tests. |
| ECOM-02 | High | app/Services/EcommerceDispatchService.php::createMultiLegDelivery | Three legs created without an enclosing transaction; default distances/fees and partner selection are hardcoded. | Open: transaction, validated routing and rate provenance. |
| APP-01 | High | capacitor.config.json; package.json | Native app build and platform workflows are not established. cleartext is enabled in wrapper configuration. | Open: choose supported app scope and validate native tooling/platform permissions. |
| BUILD-01 | Medium | scripts/vercel-build.js | Composer installation failure is caught as a warning, allowing build to continue with missing backend dependencies. | Open: fail deployment on dependency failure; pinned and verified bootstrap. |
| DOC-01 | Medium | README.md | Test counts conflict (badge 230, body 143, observed baseline 259); claims exceed demonstrated integration coverage. | Open: reconcile documentation after verified workflows. |

## Repairs implemented in this stage

Carrier fallback now returns success=false / updated=false without milestone, location, signature or delivery claims. Existing sync exits before writes on that result. Carrier portal links remain available through the gateway.

MAWB synchronization no longer advances cargo based on hours since flight or equates flight landing with customs clearance/release. Airborne data may advance an assigned/unused MAWB to in_transit. Cleared MAWBs cannot regress from airborne telemetry.

Existing carrier and flight success tests now provide explicit external telemetry fixtures instead of depending on invented events. New regression tests cover preservation of old shipment data and the inability of schedule age/landing to establish customs clearance.

Existing historical fabricated events are not automatically deleted: provenance review and an approved correction strategy are needed before modifying historical operational records.

## Workflows to validate end to end

Domestic: coverage/rate -> booking -> partner acceptance -> pickup -> origin intake -> manifest/bag -> linehaul -> destination intake -> rider dispatch -> delivery attempt/POD -> COD handover -> settlement -> closure, including reattempt and return.

International: coverage/rate/feeder -> booking/documents -> collection -> verified weight/repricing -> export intake -> manifest/MAWB -> airline handover -> verified departure/arrival -> customs evidence -> last-mile handover -> carrier evidence/POD -> billing/claims.

E-commerce: merchant identity -> quoted booking -> persisted seller ownership -> eligible rider offer/acceptance -> pickup OTP -> custody transfer -> direct delivery or sequential hub/partner legs -> recipient OTP/POD -> COD collection -> rider deposit -> merchant remittance -> return/dispute reconciliation.

These are audit targets. Their presence here does not mean every transition is currently implemented or verified.

## Ordered continuation plan

1. Close AUTH-01/AUTH-02 and PRIV-01 with negative authorization tests and migration planning.
2. Trace all money and custody mutations, enforce atomicity/idempotency, and validate shipment-state transitions across all shipment models.
3. Complete domestic, international and e-commerce workflow matrices across every operational role, including exceptions and settlement.
4. Restrict PWA caching, establish install/update/offline behavior and validate supported app capabilities.
5. Inspect every screen with role-specific journeys at desktop/mobile sizes; improve navigation, form validation, accessibility and failure states using existing branding.
6. Audit AI data access, external providers, fabricated claims, action permissions, fallbacks and evaluation. Separate deterministic assistance from actual model integrations.
7. Verify MySQL migrations, queues/scheduler, uploads, backup restoration, staging integrations and deployment rollback.
8. Reconcile each inventory entry against review evidence; publish remaining gaps and readiness decision.

## Verification limits

No production changes, real payments, carrier calls or customer notifications were intentionally performed. No secrets were printed. Browser/device acceptance, Android/iOS builds, MySQL execution, regulatory review, full accessibility review, load testing and exhaustive function review remain outstanding. Production readiness is not established.

## Verification after tracking repairs

`php artisan test --compact`: 261 passed / 2,070 assertions, 83.85 seconds. PHP syntax checks passed for both changed tracking implementations; `git diff --check` passed. The frontend build passed before these backend-only changes; generated build differences were reverted to avoid unrelated asset changes. No claim of complete workflow coverage follows from these results.
