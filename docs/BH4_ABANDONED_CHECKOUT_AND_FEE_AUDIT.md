# BH4 combined fee and abandoned checkout audit

Source-only review, 2026-11-08. No Stripe calls, database mutations or WordPress activation.

## One payment, one booking fee
- The quote computes class gross as unit class price times the number of places, then calls `parent_paid_fee_preview(gross)` **once**.
- The fee calculator grosses up the complete class gross for the configured platform percentage and estimated Stripe percentage/fixed fee. This is a **preview**, not a guarantee of actual processing costs.
- Stripe Checkout creates a **single** line item (`quantity=1`) with `unit_amount=parent_total`. It uses a per-booking Stripe idempotency key and validates Stripe's returned amount and currency.
- Destination transfer is intended to equal advertised class gross, with the parent-facing fee allocated as the application fee; verify actual Stripe Connect settlement and refunds before launch.

## Abandoned checkout: important gap
- Reservations start `reserved/pending` with a 35-minute hold. Stripe Checkout is configured to expire in approximately 30–31 minutes.
- Availability counts **reserved** bookings even when the reservation expiry timestamp has passed, until `booking_status` changes to `expired`.
- `transition_booking_status(..., 'expired')` exists, but it is a staging-only **admin** action gated by `BH3_ENABLE_BOOKING_STATUS_TRANSITIONS`.
- A source hook inventory previously found **no cron or Action Scheduler registration** in this plugin. Therefore **automatic release of abandoned holds is not established**. Do not claim it exists or turn on expiry transitions without verified reconciliation against Stripe.
- Payment arriving after hold expiry requires manual review, to avoid overselling.

## Before any staging activation
1. Design a safe expiry worker that checks Stripe session/payment status and serialises under the same booking-level lock as webhook confirmation.
2. Never expire a reservation with paid/processing/unknown Stripe status; require reconciliation and an auditable decision.
3. Ensure expiry worker cannot race a verified paid webhook, and retry failures without releasing capacity prematurely.
4. Test multi-child £5-per-place bookings with one customer-facing fee; compare quote to actual Stripe test-mode settlement.
5. Test abandoned, cancelled, failed, delayed-success, asynchronous payment and duplicate-webhook cases.

**Decision:** keep current checkout and capacity logic unchanged; automatic abandoned-hold release is a launch blocker until verified.
