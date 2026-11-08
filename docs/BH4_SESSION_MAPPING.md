# BH4 activity/session to Events Manager mapping

The new `BH4_Session_Mapping::propose($activity_id, $session_id, $event_id = null)` method accepts only positive integer identifiers and returns an **unverified mapping proposal**.

It does not read or modify any table, create Events Manager events, register a WordPress hook, inspect children, or create bookings. It is intentionally not wired into the current plugin bootstrap.

## Proposed identity mapping

- Bubba Hub activity: existing activity primary key (do not infer from slug).
- Bubba Hub session: existing session primary key (do not infer from date or display title).
- Events Manager event: optional known event ID, which must be independently checked.
- No assumption that one recurring BH session always maps to exactly one Events Manager event or recurrence. Actual model must be verified first.

## Required validation before a mapping can be trusted

1. Verify activity exists in the correct existing table and is permitted for booking.
2. Verify the session belongs to that activity; inspect actual relationship columns.
3. Verify Events Manager event exists and represents the same date, local time, timezone, recurrence and venue.
4. Check price, capacity, availability and term-time rules.
5. Define cancellation, exception dates and duplicate detection.
6. Confirm no checkout, personal-data or payout semantics change.
7. Store mappings only after schema and migration approval; no new table or metadata has been created.

The output explicitly sets `event_creation_allowed=false` and `booking_migration_allowed=false`.

## Next

Build an isolated read-only test harness with fixture IDs and inspect actual schema definitions and Events Manager's installed version/API. Keep the existing checkout unchanged.
