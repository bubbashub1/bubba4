# BH4 checkout and webhook lock audit — corrected source findings

Reviewed `class-bh-marketplace-settings.php` on 2026-11-08. Source-only; no runtime changes.

## Actual lock ownership
- `transition_booking_status()` (around line 614): `bh3_status_` + md5(database prefix, booking ID), with `finally` release.
- `confirm_verified_test_payment()` (around line 2167): **the same `bh3_status_` lock**, with an under-lock payment recheck and guarded update. **Correction:** prior migration notes incorrectly described the webhook as using a separate confirmation lock. The webhook and admin status transitions already coordinate on this lock.
- `create_test_checkout()` (around line 754): **different** `bh3_checkout_` lock. It checks payment state and session ID after acquiring the lock, but its booking state query does not select `booking_status`. A future expiry worker using `bh3_status_` cannot assume checkout creation is serialised with it.
- `reserve_occurrence()`: per-session-occurrence `bh3_` capacity lock; retain this for capacity admission.

## Required design correction
A future expiry worker should use the **existing `bh3_status_` lock** shared with webhook confirmation, not introduce an incompatible `bh4_booking_` lock while BH3 is running. The new `BH4_Booking_Shared_Lock_Contract` remains an unbootstrapped **proposal**, not a deployed lock identity.

Before activating expiry:
1. Bring checkout creation into the same booking-level lock domain or prove equivalent atomic safeguards.
2. Recheck Stripe Checkout/payment and the event ledger under that lock.
3. Require `reserved/pending`, elapsed hold and verified unpaid terminal Stripe state before an atomic expiry update.
4. Confirm that late/async payment and retries cannot release an occupied place.
5. Run real staging concurrency tests before enabling a scheduler.

**Do not migrate lock names unilaterally.** Existing webhook/status coordination is a safety property worth preserving.
