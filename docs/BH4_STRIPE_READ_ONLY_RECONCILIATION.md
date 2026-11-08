# BH4 read-only Stripe Checkout reconciliation plan

The isolated `BH4_Stripe_Session_Candidate_Classifier` reviews a **supplied** collection of Checkout sessions. It does not query Stripe, create payments, alter reservations, or guarantee the collection is complete.

- Match both booking metadata ID and client reference. A candidate matching only one is a conflict requiring review.
- Reject test/live mismatch, wrong amount/currency, malformed IDs, paid/processing/expired states.
- Treat multiple distinct matching session IDs as a duplicate-session incident; never pick newest or first.
- Even a unique open/unpaid candidate needs the separate recovery contract's expiration validation and server-side verification before reuse.
- A zero-match result is **not** proof that Stripe created no session; incomplete pagination, account scoping, or API failures must not authorize a new checkout.

## Required integration gates
1. A test-mode-only server-side Stripe lookup with complete pagination and known account scope; no parent data in diagnostic logs.
2. Compare trusted booking reference, metadata, amount, Connect destination and creation time.
3. Verify uniqueness and session expiry; cross-check event ledger and payment intent state.
4. Under `bh3_status_` lock, reload booking and conditionally persist verified session ID, never overwrite paid or expired state.
5. Provide admin-only manual recovery for ambiguous or duplicate sessions, with a clear audit trail.
6. Simulate Stripe timeout, delayed webhook, duplicate sessions, and API pagination failures in staging.

**Status:** pure classification and fixtures only. No runtime reconciliation enabled.
