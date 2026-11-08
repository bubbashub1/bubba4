# BH4 booking registration review — first callback pass

**Source-only; no staging execution or checkout changes.** Review of `includes/class-bh-leader-bookings.php` in the Bubba4 feature branch.

## Confirmed
- `BH_Leader_Bookings_3::register()` conditionally registers `[bh_leader_bookings]` and `[bh_leader_booking_dashboard]` only if the shortcode name is not already owned. This is a **fallback**, not evidence of a complete booking engine.
- `render()` is hard-restricted to the staging hostname, then requires a logged-in user with `read` capability.
- Leader organiser scope comes from `bh3_organiser_id` user metadata. Administrator-only preview can select from a bounded list of organisations.
- It reads activities, weekly session rules, optional shared session listing links, term ranges and booking/enquiry configuration. The visible calendar explicitly says it does **not** accept reservations, attendee data, reminders or payments.
- The code uses `$wpdb->prepare()` for user/ID-dependent query values; dynamically constructed table names use WordPress prefix and known suffixes. Actual staging schema is unverified.
- There are two independent booking shortcodes in this file, but the presence of the fallback does not establish who owns the live booking checkout.

## Follow-up risks
1. Verify the original booking/checkout shortcode owner and form integration separately; do not switch to Events Manager based on the calendar fallback.
2. Check every booking/payment callback for idempotency, authentication, nonce/CSRF, ownership, capacity, data minimisation, payment webhook signature and refund semantics.
3. Confirm whether the admin-only organisation preview can expose booking-related private data when later extended; currently it renders schedule data.
4. Verify that all session-listing link queries are correctly scoped to the selected organiser before adding booking records.
5. The source scan found five booking/payment-related hook registrations and four booking-related shortcodes overall; this review covers **two shortcodes**, not the remaining seven sites.

**Decision:** keep existing checkout owner unchanged. Staging and payment integration remain unverified.
