# Bubba4 booking compatibility acceptance matrix

This is a development contract, **not** proof that Events Manager satisfies Bubba Hub's booking requirements. All twelve checks start as `not_tested` and replacement readiness remains `false`.

| Requirement | Expected evidence |
| --- | --- |
| Activity and session selection | Correct activity/session identifiers and timetable in test checkout |
| Multiple children, one booking | Two or more child profiles attached to one parent checkout |
| Guest and member checkout | Both paths complete with appropriate permissions |
| Guardian details and consent | Explicit consent fields, controlled leader visibility |
| One Stripe payment | One PaymentIntent/charge for the entire basket |
| Single displayed booking fee | Displayed total matches charged total, fee not double-counted |
| Leader receives advertised price | Ledger and payout reconciliation for a sample booking |
| Capacity and duplicate prevention | Simultaneous reservations and replay tests |
| Confirmation and cancellation | Parent and leader receive correct lifecycle updates |
| Refunds and failed payments | Refund policy, webhook replay and failure states tested |
| Leader dashboard visibility | Enrolment and attendee data correct and access-controlled |
| Privacy and retention | Export, erasure and retention behaviour verified |

## Safety boundaries

- `BH4_Booking_Compatibility` is a passive PHP class, not loaded by the existing bootstrap.
- No events, booking records, personal information or payment records are read or written.
- A plugin being installed or active never counts as a passed check.
- Preserve `[bh_parent_booking]`, Fluent Form 8, Stripe settings and existing leader booking dashboard until isolated end-to-end tests pass.
- Do not deploy the migration branch or enable schema changes yet.
