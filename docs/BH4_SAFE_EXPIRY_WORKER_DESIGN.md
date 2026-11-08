# BH4 safe abandoned-hold worker design (not active)

**Status:** design only. No cron, Action Scheduler, Stripe API call, schema migration or runtime booking mutation has been added.

## Required sequence
1. Scheduler finds a small batch of `reserved/pending` holds whose expiry timestamp elapsed. It must not infer unpaid from elapsed time.
2. Obtain a **single booking-level lock shared with payment reconciliation**. Existing code uses separate lock names (`bh3_status_...`, `bh3_checkout_...`, and `bh3_confirm_...`), so simply reusing the status lock is **not sufficient**. Standardise locking before activating any worker.
3. Reload booking and Stripe Checkout session ID under lock. Recheck `booking_status`, `payment_status`, expiry and relevant event ledger.
4. If Checkout exists, retrieve session/payment status using server-side test credentials. Treat open, processing, pending asynchronous payment, retrieval failure, unexpected or inconsistent data as **manual review / retry**, never automatic release.
5. Only a verified `expired` and unpaid Checkout session (or a provably absent Checkout session after a protected checkout-creation check) is eligible. Call the pure `BH4_Reservation_Expiry_Contract::decide()`.
6. Atomically transition `reserved/pending` to `expired/pending` with a WHERE guard on status, expiry and booking ID. Record a non-sensitive audit entry.
7. Release lock, return a bounded summary; retry uncertain states without changing capacity.

## Race conditions to resolve
- Existing checkout creation uses a `bh3_checkout_` lock; payment confirmation uses another lock; admin status transitions use `bh3_status_`. These do **not** mutually exclude one another. A worker needs shared locking or an equivalent atomic state protocol with the webhook handler.
- A Stripe event can be received before local ledger processing. Verify event ledger and Stripe payment state before expiry.
- An unpaid session may be in an asynchronous payment state; do not release its place until final failure or an explicit reviewed refund/settlement outcome.
- The existing capacity query deliberately counts all `reserved` bookings, including elapsed ones. This is safe but can strand places until a reconciled transition occurs.

## Deployment gates
- Dedicated staging-only feature flag, disabled by default.
- Read-only dry-run report before writes.
- Stripe test-mode-only reconciliation and replay fixtures.
- Shared locking and concurrency tests, including delayed webhooks.
- Admin-only recovery and audit visibility; no sensitive parent data in logs.
- Explicit sign-off before activating scheduling or modifying any existing booking.

**Next implementation step:** create an isolated read-only worker planner that produces safe candidates for reconciliation, then test it. Do not register a scheduled job until lock and webhook parity is proven.
