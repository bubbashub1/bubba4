# BH4 checkout, webhook and expiry lock audit — current branch

Source reviewed on 2026-11-08; no runtime payment tests or scheduler activation.

## Verified current source
- `create_test_checkout()` acquires the **shared `bh3_status_` booking lock**, then re-reads `booking_status`, `payment_status`, checkout session and reservation expiry. It requires `reserved/pending` and no recorded Checkout session.
- `confirm_verified_test_payment()` uses the same `bh3_status_` lock and rechecks the payment state.
- Administrative booking transitions use the `bh3_status_` lock. Per-occurrence capacity reservation uses its own capacity lock.
- This corrects an **older checkout audit** that described checkout as using `bh3_checkout_`. That finding was superseded by later code changes. Do not rely on the older lock description.

## Still blocked
- A shared lock does not guarantee Stripe's remote response is recorded after a timeout. Idempotency replay, lost responses and ambiguous Checkout sessions still need reconciliation.
- The external Stripe request may hold a lock longer than other operations wait; timeout/concurrency testing is required.
- No expiry scheduler or automated reminder job should be enabled until Stripe state, event ledger, lock ordering and late webhook handling are proven under staging concurrency tests.
- Existing test checkout is staging-only and gated by feature flags; do not switch to production keys.

## Required staging checks
1. Parallel checkout requests for the same booking.
2. Webhook arriving while checkout is being created.
3. Checkout timeout followed by safe idempotent retry.
4. Expired unpaid Checkout session versus pending asynchronous payment.
5. Delayed duplicate webhook and a manually confirmed/enrolled booking.
6. Reservation expiry race with a successful payment; no released paid place.

No booking, database, cron, webhook or Stripe settings were changed in this audit.
