# BH4 Stripe idempotency and lost-response safety

The existing staging Checkout request sends `Idempotency-Key: bh3-test-checkout-{booking_id}` to Stripe. The key helps protect retries but **does not prove** whether a timed-out request created a session. Stripe may retain idempotency records for a limited period; do not assume a historical key guarantees safe replay indefinitely.

## Safe recovery path
1. On ambiguous HTTP timeout, retain the booking hold while investigating; never automatically issue a new Checkout session or mark payment failed.
2. If a Stripe Checkout session ID is saved, retrieve and validate it.
3. If ID was not saved, a server-side reconciliation procedure must find a uniquely matching Stripe test session using trusted booking metadata/reference and creation window, or otherwise require manual review. Do not infer nonexistence from an incomplete list.
4. Validate test mode, account, amount, currency, booking ID, reference, status, expiration, and payment destination before persisting or showing a recovered session.
5. Under `bh3_status_` lock, reload the booking, check it remains `reserved/pending`, and atomically persist only an appropriate verified session.
6. For paid, processing, expired, duplicate or conflicting sessions, stop and reconcile payment/capacity; never start a second charge.

`BH4_Stripe_Idempotency_Replay_Contract` is a pure fail-closed decision helper; it **does not** perform Stripe search or retries. It intentionally never authorizes blind replay after an ambiguous creation outcome.

**QA gates:** Stripe test-mode integration fixtures, account/Connect verification, delayed webhooks, 5-second lock contention vs 25-second Stripe request, key retention boundary, and manual recovery before enabling runtime integration.
