# BH4 admin booking reconciliation report — planned

**Not active.** The pure `BH4_Booking_Reconciliation_Report` formats pre-validated decisions only. It does not query WordPress, Stripe, family records or booking tables, and it does not render a WP admin page.

## Intended admin-only review
- Show booking ID, conservative action classification and machine-safe reason.
- Never show child name, birth year, parent contact details, payment tokens, Stripe secrets or raw webhook bodies in the diagnostic list.
- Restrict future report access to a dedicated administrator capability and require nonce protection for any action.
- Show an explicit `read-only` label and separate reviewed recovery from automated decision-making.
- Bound report size, paginate and retain a minimal audit history of reviewer and outcome.

## Workflow
1. Collect eligible booking IDs via a read-only, bounded query.
2. Reconcile server-side Stripe test sessions, payment intents and verified event ledger.
3. Classify zero/one/multiple sessions, timeouts, missing metadata and paid/processing states.
4. Format only allowlisted fields and safe reason codes for admins.
5. Any later recovery action requires an independent under-lock validation, explicit permission and an audit record. The report itself cannot change booking or payment status.

## QA
Verify access denial for unauthenticated and leader/family users; ensure no personal data leaks in logs, exports or error messages; test pagination, duplicate sessions and incomplete Stripe responses.

No admin page, background job, Stripe lookup or database write has been enabled.
