# NETPACK release review

23 October 2026 · Asia/Katmandu

24 October UI follow-up: the previous visual styling has been replaced by a new shared design system, public homepage and sign-in experience. See [UI_REDESIGN.md](UI_REDESIGN.md) for scope, screenshots and current UI verification. Earlier inventory numbers below describe the audit snapshot on 23 October.

## Release decision

The working local preview is at http://127.0.0.1:8088, using isolated MySQL data and demo accounts. This is not an internet production deployment. Production release is NOT approved by this report.

Inventory: 862 source/configuration files, 2,318 named PHP declarations and 589 routes. Inventory is not exhaustive behavioral certification. Unreviewed declarations remain marked in function-inventory.csv. Closures, scripts, views, integrations and infrastructure also need verification.

## Latest verification

- Full MySQL 8 suite: 277 passed, 2,165 assertions (106.32 seconds), using the isolated netpack_audit_tests database.
- Full SQLite suite before the final partner-isolation test: 276 passed, 2,162 assertions. The final partner-rate suite separately passed all eight tests / 47 assertions on SQLite.
- Five PWA cache-boundary tests passed. Final frontend production build and Blade compilation passed. Git whitespace checks are clean.
- Composer security audit and production-only npm audit passed. The full npm audit still has the build-tool advisories listed below.
- Browser inspection verified the rate editor and assistant drawer at the available narrow viewport. Desktop and device acceptance across every role remains outstanding.

MySQL verification also repaired the overseas hub sort column, pickup tracking lookup, missing order-to-delivery foreign key, and product sales aggregation. Partner zone writes and rate queries now use partner_user_id for user ownership instead of assuming users and domestic_partners share IDs. Cross-partner rate access is rejected. Historical ownership still requires explicit reconciliation.

## Completed repairs

| Area | Result | Evidence |
|---|---|---|
| Carrier tracking | Missing evidence cannot invent scans, signatures or delivery. | TrackingEvidenceSafetyTest |
| Flight/customs | Elapsed time and landing cannot establish clearance or cargo release. | Flight evidence tests |
| Carrier callbacks | Per-carrier HMAC relay signature, five-minute tolerance, throttling, replay cache, transactional persisted deduplication and carrier/waybill binding. | CarrierApiIntegrationTest; authorization regressions |
| Seller isolation | Persisted seller ownership controls listing, details and ratings. Legacy ownership is not guessed from names/phones. | Cross-seller collision tests |
| Delivery integrity | Locked acceptance/completion; eligibility, capacity, reserved COD and predecessor checks; retries cannot duplicate collections or earnings. | OperationalIntegrityTest |
| Returns | Failed delivery requires collected custody. Return jobs retain the carrying rider and capacity. | Rider lifecycle/return tests |
| Multi-leg continuity | Hub desk records linehaul dispatch/receipt with actor, location and reference; final delivery waits for prior legs. | Three-leg lifecycle test and handover event records |
| Recipient codes | Seller can share a signed 48-hour handover link. Code is revealed after pickup; links are private and expire. | Signature, expiry, pre-pickup secrecy tests |
| COD deposits | Duplicate references cannot duplicate deposits; pending deposits reserve balance; approval locks records and prevents over-crediting. | Deposit regression tests |
| Payments | Corrected Khalti amount and eSewa URL/status/amount/merchant contract; actual initiation route; optional seller for customer-only capture. Replayed/late callbacks preserve shipment progress. | PaymentGatewayContractTest; PaymentCaptureSafetyTest |
| KYC privacy | Private uploads, authorized preview and verify-before-delete legacy migration command. | Document access and migration tests |
| Waybill privacy | Public tracking remains available; full waybills require authenticated shipment access. | Tracking/HAWB tests |
| AI operations | Administrative authorization; actions record follow-up rather than claiming unperformed notifications or delay resolution. | AI authorization/action tests |
| PWA | Only explicit public assets cached; private operational data bypasses cache; old NETPACK caches purged; safe notification targets. | Five Node PWA tests |
| Bootstrap | Removed forced copied SQLite and hardcoded deployment key; web process respects configured database. | Browser login against MySQL |
| MySQL schema | Role/status columns accept application vocabulary that old enums rejected; data preserved; unsafe narrowing rollback refused. | MySQL integration runs |
| Dependencies | Compatible PHP/npm patches; build tools classified as development dependencies. | Composer and production npm audits |
| UI/UX | Shared neutral theme, quieter navigation, restrained teal, clearer typography, fewer effects, opt-in voice, calmer dashboard/rate editor and associated form labels. | Browser inspection, Blade compilation, regression tests |

## Deployment changes

Apply all five new migrations before serving the updated application, after database/storage backups. Assignments with null seller_id require evidence-based mapping by operations; do not guess ownership from matching names or phone numbers.

Inventory legacy public KYC with `php artisan storage:privatize-kyc`. During maintenance run `php artisan storage:privatize-kyc --apply`. Private copies are verified before removing originals. Separately reconcile older public POD, deposit-receipt and other sensitive upload paths; this command moves only KYC files.

Rotate any deployment using the previously published demo APP_KEY, planning for encrypted sessions and data. Supply a unique key via deployment secrets; vercel.json no longer embeds it. Demo credential buttons are local/testing only; demo seeding is blocked elsewhere.

Carrier integrations must adopt the relay contract below. A native carrier signature is not the same as the NETPACK relay signature. Provider-specific adapters need real integration acceptance.

Serverless deployments need durable external database/storage and shared sessions/cache. Queue workers and scheduler require an appropriate worker environment. Cold-start SQLite copies are not a production datastore.

## Signed relay contract

`POST /api/webhooks/carrier-tracking/{carrier}` accepts configured supported carrier slugs. Set a distinct environment secret of at least 32 characters per carrier. Send JSON containing tracking_number (assigned last-mile waybill), status, location, optional description and ISO timestamp.

- `X-Netpack-Timestamp`: Unix seconds.
- `X-Netpack-Signature`: hex HMAC-SHA256 of timestamp + `.` + the exact raw JSON body, using that carrier's secret.

This is NETPACK's trusted-relay contract. Native carrier callbacks must be verified and translated by their adapter. Unknown statuses are rejected; terminal shipment states preserved; repeated payloads do not append duplicate events.

## Outstanding production acceptance

- Complete remaining declaration/file review and role-by-role browser acceptance. The inventory deliberately does not label every function audited.
- Approve actual coverage, carrier contracts, service promises, taxes, linehaul pricing, credit and merchant remittance rules. Some existing e-commerce distances/prices remain defaults requiring approved configuration.
- Validate actual merchant sandbox initiation, callbacks, lost-session recovery, refunds and reconciliation. Current tests use fixtures, not provider certification. Payment callbacks currently require the customer's session; recovery/reconciliation needs staging validation.
- Configure recipient notification delivery. Manual sharing of the signed handover link works; an SMS integration is not established.
- Complete merchant payouts and finance reconciliation against approved accounting rules. The existing SplitPaymentService intentionally defers payouts: capture does not constitute an automatic merchant payout. Do not advertise instant settlement.
- Reconcile all existing sensitive uploads, including legacy POD/deposit files still using public paths, before internet operation.
- Review historical tracking provenance and corrections; previously fabricated events were not silently rewritten.
- Complete Android/iOS platform projects, builds, permissions and device testing. Capacitor configuration alone is not a completed app. PWA transactional offline queues are not implemented.
- Seven npm advisories remain in the Tailwind 3 build chain (five high, two moderate). A major Tailwind migration and visual regression pass remain necessary for a clean full dependency audit. Production npm audit is clean at this review.
- Verify production secrets, HTTPS, durable workers/scheduler, monitoring, restore-tested backups and rollback on the actual target environment.

## Provider references checked

Payment contract corrections were checked against [Khalti checkout/lookup](https://docs.khalti.com/khalti-epayment/) and [eSewa ePay](https://developer.esewa.com.np/pages/Epay). These describe contracts, not certification of NETPACK's merchant accounts.
