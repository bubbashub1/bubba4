# BH4 checkout lock hardening — development branch only

On 2026-11-08 the staging-gated `create_test_checkout()` handler was changed on the unmerged Bubba4 branch:

- Its booking-level lock now uses `bh3_status_` (same database-prefix/booking-ID identity as verified Stripe confirmation and admin status transitions), replacing `bh3_checkout_`.
- The under-lock booking reload now includes `booking_status` and rejects anything except `reserved/pending`.
- The guarded SQL write of `stripe_checkout_session_id` additionally requires `booking_status='reserved'`.
- The existing occurrence-level capacity-admission lock remains unchanged.
- No expiry scheduler, Stripe reconciliation worker or database migration was activated.

**Risks and required QA:** this does not prove safe real-world concurrency. Run test-mode staging races between checkout creation, paid webhook and admin cancellation/expiry; verify timeouts, Stripe idempotency retries and no capacity release for paid or processing sessions. The external Stripe HTTP call currently occurs while the booking lock is held (up to 25 seconds), potentially causing 5-second lock acquisition timeouts for simultaneous handlers. This must be reviewed before launch. Legacy `bubbawp` is untouched.

The older source audit documenting separate checkout and status locks describes the *pre-change baseline*, not the branch's current state.
