# BH4 exact booking lock ownership review

Source inspection 2026-11-08; no runtime change.

## Important correction
The Stripe paid-confirmation handler **already uses the same `bh3_status_` booking lock as `transition_booking_status()`**. This prevents those two paths from simultaneously changing a given booking, provided both acquire the lock successfully. Earlier broad descriptions that these two handlers use different locks were inaccurate.

| Operation | Lock identity | Notes |
| --- | --- | --- |
| Reserve session places | `bh3_` + MD5(prefix, session ID, occurrence date) | Serialises new capacity admissions |
| Create Stripe Checkout | `bh3_checkout_` + MD5(prefix, booking ID) | Distinct from status/confirmation lock |
| Admin booking status transition | `bh3_status_` + MD5(prefix, booking ID) | Staging-only, gated |
| Verified Stripe payment confirmation | `bh3_status_` + MD5(prefix, booking ID) | **Matches status transition**; rechecks payment and hold under lock |
| Future expiry worker | Not implemented | Must use `bh3_status_` or a coordinated migration, plus checkout-session reconciliation |

## Remaining real race
Checkout creation uses `bh3_checkout_`, so it is not mutually exclusive with a status transition. Its conditional update guards payment status and null checkout ID but does not independently guard `booking_status='reserved'` in that UPDATE. A future expiry worker must not infer `no_checkout_session` from an unlocked snapshot while checkout creation is underway.

## Safe next steps
1. Do **not** blindly rename the existing payment and status locks to `bh4_booking_`: their existing shared name already protects confirmation versus admin transition. A one-sided rename would introduce a race.
2. Before any worker is active, reconcile Stripe state and coordinate with checkout creation. Consider migrating checkout creation to `bh3_status_` with a carefully tested lock order, or add an equivalent atomic state protocol.
3. Retain the occurrence-level lock for admitting new reservations.
4. Add staging concurrency fixtures and payment-webhook tests before modifying runtime or scheduling expiry.

This review supersedes the lock-identity claims in `BH4_SAFE_EXPIRY_WORKER_DESIGN.md` and `BH4_SHARED_BOOKING_LOCK_MIGRATION.md`; those earlier documents should be read with this correction.
