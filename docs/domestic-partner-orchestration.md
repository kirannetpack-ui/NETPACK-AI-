# Domestic Partner Orchestration

This document describes the implemented domestic pickup and delivery workflow. The design keeps the existing Laravel monolith and legacy data intact while making `users.id` the canonical identity for every authenticated domestic partner.

## Operating model

1. An approved domestic partner creates an operating territory.
2. The territory remains inactive and pending until an administrator approves it.
3. The partner submits one or more rate types for an approved origin/destination lane:
   - pickup;
   - inter-zone logistics;
   - last-mile delivery;
   - complete door-to-door.
4. The submission notifies Super Admin, Admin, Domestic Admin and domestic/all-scope staff. Pending or rejected rates can never be returned in a client quote.
5. An administrator reviews the lane, service, weight band, surcharges, SLA and effective dates. Approval activates the rate; rejection records the reason. Every decision is written to `domestic_rate_events`.
6. The administrator may assign one default partner and multiple approved alternatives for each territory, service and delivery leg. Changing the default does not delete alternatives.
7. A client selects origin and destination territories. Kathmandu is sorted first as a convenient default gateway, but it is not mandatory.
8. The quote engine uses approved, active and date-valid partner rates only. It either selects a complete door-to-door rate or composes pickup + inter-zone logistics + delivery legs. Admin percentage/fixed margin is added before the price is shown to the client; partner cost is not returned by the client quote API.
9. Booking freezes the selected rates, costs, margin and client price into `shipment_legs`, creates the linked pickup request, and notifies the assigned partner plus domestic operations.
10. Partners update only their assigned legs. The state machine rejects impossible jumps such as Assigned → Completed. Status changes update customer tracking and create `shipment_leg_events`.
11. A partner may forward an assigned/accepted/exception leg only to another partner approved for that territory, service and leg. The handoff preserves the previous partner, new partner, actor, reason and timestamp.

## International first mile

International booking continues to work without a domestic lane, preserving the existing workflow. When an origin territory and gateway territory are selected, the system adds the approved domestic pickup/logistics quote to the international price and creates the domestic route legs. This supports dispatch to Kathmandu or any other configured gateway.

## Remote areas and COD

Manual remote-area surcharge and COD handling remain configurable on every partner rate. These values are included in partner cost and customer margin calculations. Carrier API integrations are deliberately not simulated; future carrier adapters must use official APIs or approved data files and write the source and last-updated date.

## Deployment order

1. Back up the staging database.
2. Deploy the code and run `php artisan migrate --force`.
3. Run `php artisan optimize:clear` followed by `php artisan optimize`.
4. Approve or create territories.
5. Configure default and alternative partner assignments in **Admin → Domestic Logistics → Partner Routing**.
6. Have partners submit their rate lanes, then approve them in **Admin → Domestic Rates**.
7. Test a client quote and booking, confirm the partner notification, and advance the assigned leg through its valid statuses.

The migration backfills canonical partner user IDs by matching legacy domestic-partner and user email addresses. Legacy ID columns remain in place for backward compatibility and are not destructively rewritten.
