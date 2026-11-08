# BH4 Form 7 / Form 8 checkout identity review

**Source-only, 2026-11-08. No WordPress form submissions or Stripe payments.**

## Findings
- The current marketplace checkout selects the form with `get_option( 'bh3_booking_form_id', 8 )`. The default is **Form 8**, not Form 7.
- The `[bh_parent_booking]` widget renders `[fluentform id="<configured ID>"]` only when booking is enabled and the shortcode is installed.
- `fluentform/submission_confirmation`, dynamic `family_profile_ids` choices and hidden session/context fields all check the **configured form ID**. Their method names still say `form7` but do not hard-code 7.
- The confirmation handler verifies an authenticated parent and a signed, session-and-user-bound context token before calling the bound test checkout method. It only redirects to a verified `https://checkout.stripe.com/` URL.
- The configured form ID is admin-editable and checked against `fluentform_forms` before saving.
- A legacy test and JSON export still specifically cover **Form 7**. Passing that test does not establish that the currently selected **Form 8** has the required fields and consent configuration.
- `BH_Booking_Form7_Owner_3` separately validates against `BH_Booking_Consent_Contract_3::FORM_ID`; check that legacy consent-only workflow before replacing any form or changing the constant.

## Staging acceptance checklist
1. Read the configured `bh3_booking_form_id` option (expect 8 only if no override).
2. Inspect the **actual selected form** for session_id, occurrence_date, places, family_profile_ids, booking_context_token, parent_name/email, booking_terms_consent and sharing consent.
3. Verify session-bound token, logged-in customer ownership, saved submission, multi-child selection and price/places consistency.
4. Test negative cases: wrong form ID, stale context, another parent's submission, missing consent, zero/too many places and double-click.
5. Confirm a single Stripe test Checkout session is created, and only a verified paid webhook confirms the booking.
6. Verify guest booking remains explicitly unsupported unless separately authorised.

**Decision:** retain Form 8 as default, preserve Form 7 consent legacy paths, do not automatically migrate form records. This is not staging verification.
