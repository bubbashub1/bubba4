# BH4 Stripe Checkout session recovery: isolated contract

A timeout during Stripe Checkout creation is ambiguous. Do not create another payment session or release capacity based solely on an HTTP timeout.

The pure `BH4_Stripe_Session_Recovery_Contract::verify()` accepts only a retrieved **test-mode** Checkout session with matching booking metadata, reference, GBP amount, an open/unpaid status and a future expiry. It makes **no Stripe API request** and does not update a booking. Recovery of paid, completed, expired, missing or mismatched sessions requires a separate reconciliation path.

Before wiring into checkout:
1. Design a server-side lookup path for a Stripe session when the ID was not persisted. A booking ID is **not** itself a Stripe session ID; retrieval by ID is unavailable if that ID was lost. Investigate safe recovery using Stripe idempotency replay with identical parameters and retention constraints, or a server-side search/list workflow with explicit identity checks.
2. Confirm full Stripe metadata and customer/organiser ownership, account, session expiration and Connect destination before showing any recovered URL.
3. Reconcile under the existing `bh3_status_` booking lock and never overwrite a paid/expired booking.
4. Test network timeout, Stripe API failure, duplicate attempts, late paid webhook and idempotency-key expiry in staging.
5. Keep the active runtime unchanged until these paths are proven.

**Current status:** fixture-only contract, not a working Stripe recovery feature.
