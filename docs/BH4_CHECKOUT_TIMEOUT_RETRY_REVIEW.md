# BH4 checkout timeout and retry review

Development-only, no active Stripe integration.

Checkout creation now uses the same booking-level lock as payment confirmation and status changes, but the existing code makes a Stripe HTTP request with a 25-second timeout while holding a MySQL lock whose competing acquisition timeout is 5 seconds. This can cause transient busy errors; it is **not** proof of double charges.

A network timeout or lost HTTP response is **ambiguous**: Stripe might have created a session even when WordPress has not stored its ID. The existing booking-ID-based Stripe idempotency key helps, but do not treat it as sufficient proof that a retry is safe, especially after changed request parameters or idempotency retention windows.

The isolated `BH4_Checkout_Retry_Contract` requires reconciliation of ambiguous outcomes rather than automatically creating another session. It does not perform Stripe lookups, persist sessions or acquire locks.

Before staging activation: verify Stripe test-mode session retrieval/recovery, idempotency replay with identical parameters, timeout handling, paid/expired states, and lock contention. Do not expose a checkout URL without validating session ownership, amount, currency and test mode.
