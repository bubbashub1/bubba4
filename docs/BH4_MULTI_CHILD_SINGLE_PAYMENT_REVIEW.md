# BH4 multi-child / one-payment checkout source review

Read-only source inspection, 2026-11-08. No staging database access or payment requests.

## Confirmed source contracts
- `inspect_booking_attendees()` requires an authenticated owner, 1–20 places, exactly one selected family profile per place, unique IDs, and matching user-owned records. It supports child and bump profiles.
- `inspect_booking_form_fields()` checks the saved submission's selected places, session and occurrence against the server expectation.
- `reserve_occurrence()` validates date/session and uses a per-session-occurrence database lock before checking capacity, revalidating price and inserting one temporary reservation with a 35-minute expiry.
- The Stripe test Checkout payload has **one** line item with `quantity=1` and `unit_amount=parent_total`; it does **not** create a separate Stripe payment for each child. The parent total is computed from server-side gross price for all places plus a single combined booking fee.
- The checkout creator checks stored attendee count equals places and both required consents before payment; it also uses a per-booking lock and Stripe idempotency.
- Redirect to My Hub is not payment confirmation; signed paid webhooks govern confirmation.

## Remaining staging risks
- Verify `bh_family_profiles` exists and has `gender` plus required ownership fields, without altering tables. Legacy `bh_children`/`bh_bumps` remain protected.
- Test two child profiles and a child-plus-bump booking, both under one parent; verify exactly one Stripe test Checkout and correct total.
- Test duplicate profiles, foreign profiles, stale submission, missing consent, full capacity, concurrent parents and a failed payment.
- Confirm the server-side fee assumptions and Stripe Connect destination account with test-mode fixtures.
- Check expiry/Stripe Checkout timing: reservation hold is 35 minutes, while checkout requires at least 30 minutes remaining. Delays may legitimately fail closed.

**Decision:** preserve existing code and database. Source contracts appear designed for multiple profiles and one payment, but end-to-end readiness is unverified.
