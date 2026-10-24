# NETPACK interface redesign

24 October 2026 · Asia/Katmandu

## Direction

The new Dispatch visual system replaces the previous teal/gradient styling with an ink-navy workspace, warm white canvas, restrained copper accents and quieter typography. Real workflow state retains semantic warning, error and success colours.

The source is resources/css/workspace.css. It defines common navigation, typography, focus indicators, controls, tables, panels, public pages, authentication, responsive layouts and reduced-motion behaviour. The wordmark is a local Blade component, without a remote image dependency.

## Scope

- Rebuilt public homepage: service selection, primary booking and rate actions, tracking form, delivery process and concise navigation.
- Rebuilt account sign-in: two-panel desktop layout, compact mobile layout, password visibility and browser autocomplete. Development demo accounts remain restricted to local/testing.
- Shared workspace styling across admin, customer, seller, rider, partner, international and overseas layouts. The old independent admin layout now inherits the common application shell.
- New navigation rail, active-page semantics, accessible mobile toggle, Escape dismissal and hidden closed-drawer links.
- Refined operations overview, compact mobile service links, neutral metrics and expandable operational workbench. Its booking, status, quote and delay tools remain available.
- Rebuilt customer overview and refined merchant/rider dashboard introductions.
- Shared styling reaches registration, agency login/staff screens, payments, notifications, public tracking and other standalone web shells. Existing detailed workflow templates inherit the system; this is not a claim that every template was individually rebuilt.
- Removed duplicate runtime Tailwind loading from 16 web shells. The routing map keeps its existing Leaflet/plugin runtime and loads only the new CSS.
- Updated PWA colours, install banner and self-contained offline page. Cache version v4 refreshes public assets while retaining private-data cache boundaries.
- Replaced unsupported perfect-SLA and guaranteed-outcome copy in the empty diagnostic response with descriptions of the checks actually performed.

Printed waybills, invoices, packing lists and email templates retain their document layouts. Native Android/iOS platform projects remain a separate unfinished deliverable from the earlier release review.

## Verification

- Full automated suite: 277 passed / 2,165 assertions, 57.01 seconds (SQLite).
- Five service-worker cache-boundary tests passed, including migration from the prior public cache.
- Frontend production build and Blade compilation passed.
- Browser inspection at 1440×1000 and 390×844 verified homepage, workspace hierarchy, mobile drawer and workbench expansion. Escape returns the drawer to its closed state; closed navigation links leave the accessibility tree.
- Existing role and workflow regression tests continue to exercise the portal routes. Not every detailed page or physical device has had individual visual acceptance.

## Maintenance

Use the shared controls and surface classes rather than adding page-specific gradients, large shadows, flashing badges or new font families. Preserve visible labels, validation messages, keyboard focus and status meaning. Keep primary actions obvious and group infrequent tools behind clearly labelled disclosure controls.

Production blockers in RELEASE_REVIEW.md still apply; visual redesign does not certify carrier accounts, payments, payouts, historical data or native applications.
