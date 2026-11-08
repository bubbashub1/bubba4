# BH4 checkout hook ownership and Stripe review — source pass 2

**Read-only source review, 2026-11-08. No Stripe calls or staging activation.**

## Checkout owner identified
`BH_Marketplace_Settings_3::register()` in `includes/class-bh-marketplace-settings.php` (lines 5–29) registers the existing `[bh_parent_booking]` and `[bh_leader_payout_settings]` shortcodes, three Fluent Forms filters, admin-post payment/leader actions, and four REST registration callbacks. Registration itself is restricted to the staging hostname. The manager must call `BH_Marketplace_Settings_3::register()` for these hooks to exist: check that bootstrap call separately before claiming they are active.

- `fluentform/submission_confirmation` calls `filter_form7_checkout_confirmation`, providing the form-to-checkout handoff. The selected form defaults to ID **8** in marketplace settings even though method names and older tests refer to Form 7. Verify configured form and exported field names; do not infer form identity from function names.
- `/parent-bound-test-checkout` and `/parent-booking-bind` are POST endpoints with `is_user_logged_in()` permission callbacks and feature flag `BH3_ENABLE_BOOKING_FORM_BINDING`. Handler-level validation, submission ownership, CSRF/REST nonce and capacity locking need separate tests.
- `/native-booking-preflight` is staging-only and opt-in, requires login plus WP REST nonce, and explicitly returns `checkout_authorised=false`; it is not a payment route.
- `/stripe-test-webhook` is an unauthenticated POST endpoint **by design**; it verifies the Stripe signature in the callback. Registration requires `BH3_ENABLE_STRIPE_TEST_WEBHOOK`, and callback rechecks staging host and flag.
- The webhook ledger records unique Stripe event IDs and recognises duplicate deliveries; payment confirmation requires a separate opt-in `BH3_ENABLE_TEST_BOOKING_CONFIRMATION`. Only paid checkout events are eligible for confirmation.
- Existing isolated fee smoke tests expect £5.00 class + £0.42 fee under **test assumptions** (2.5% platform component, 1.5% + 20p estimated processing), while prior user-facing £5.50 was illustrative. Do not hardcode either as a production quote.

## Risks to test before checkout migration
1. **Bootstrap ownership:** verify whether `BH_Marketplace_Settings_3::register()` is invoked, and whether any other plugin owns `[bh_parent_booking]` at runtime.
2. **Form mismatch:** test configured Form 8 vs legacy Form 7 mapping and confirmation hook with real staging fixtures, without submitting live payments.
3. **Webhook retries:** verify what happens when the event is recorded but confirmation fails; a duplicate delivery must still retry reconciliation without duplicate notifications or charges.
4. **Payment idempotency:** test simultaneous checkout requests, abandoned holds, Stripe idempotency key and database unique constraints.
5. **REST permissions:** test session cookie + nonce requirements for state-changing routes, customer ownership, guest handling and cross-account submission IDs.
6. **Financials:** reconcile customer total, Stripe actual processing, application fee, leader payout, refunds and rounding for multiple children/places.
7. **Confirmation:** ensure a redirect never confirms a booking; only signed, reconciled, paid Stripe events do.
8. **Events Manager:** do not transfer checkout ownership until feature parity, free-vs-Pro cost and acceptance gates pass.

**Decision:** existing checkout ownership remains unchanged; no merge/deploy. This source review is not a payment security sign-off.
