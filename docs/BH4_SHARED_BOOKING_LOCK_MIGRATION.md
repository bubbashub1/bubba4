# BH4 shared booking lock migration plan

**Not active:** the new `BH4_Booking_Shared_Lock_Contract` is a pure fixture contract. It does not acquire locks, modify existing checkout handlers, release capacity or call Stripe.

## Invariant
All operations that can change a booking's payment, checkout-session or capacity-holding state must serialise by **the same booking ID lock**. A lock name must include the database prefix to isolate sites.

## Current legacy mismatch
- Reservation creation uses a session/occurrence capacity lock.
- Checkout session creation uses `bh3_checkout_` per booking.
- Booking status transition uses `bh3_status_` per booking.
- Stripe confirmation uses a separate per-booking confirmation lock.
These locks do not automatically exclude each other.

## Safe migration order
1. Keep the existing session/occurrence lock for **capacity admission**. It protects concurrent new reservations.
2. Refactor checkout creation, webhook confirmation, status transitions and future expiry processing to use the **same booking lock**, with explicit lock acquisition and `finally` release. Do not nest different booking locks or silently bypass failures.
3. Under the shared lock, reload authoritative database state; reconcile Stripe test Checkout and event ledger before any expiry transition.
4. Only release capacity after a guarded atomic `reserved/pending -> expired/pending` transition. Verified paid bookings must never be expired.
5. Exercise concurrent checkout/webhook/expiry cases in staging with test-mode Stripe and verify no deadlocks, lost payments, duplicate transfers or overselling.
6. Activate a bounded scheduler only after concurrency and recovery checks pass. Default off; provide audit trail and manual recovery.

**Known limitation:** isolated tests demonstrate deterministic lock names and conservative decision ordering, **not** real database mutual exclusion or webhook correctness.
