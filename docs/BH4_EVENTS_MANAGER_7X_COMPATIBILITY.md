# Events Manager 7.x compatibility update (8 November 2026)

Current WordPress.org plugin listing: https://wordpress.org/plugins/events-manager/

- The 7.2 changelog documents composite event UIDs like `123:456`, where the second part identifies a timeslot; `EM_Event->event_id` may expose the full UID, while `get_event_id()` returns the numeric event ID.
- The plugin advertises recurrence patterns, exclusion dates, and multiple timeslots. These capabilities still need verification against the installed staging version and Bubba Hub's actual session records.
- Stripe and other online payment gateways are described as **Pro** features. The free edition must not be assumed to provide Bubba Hub's existing Stripe checkout, fee calculation, or payout semantics.
- The WordPress.org changelog lists numerous security and booking fixes in recent 7.4.x releases. Pin and review a supported patched version before any future isolated testing.

## Source changes

`BH4_Session_Mapping::propose()` now accepts a positive numeric event ID **or** a canonical `event:timeslot` string as its optional Events Manager identifier. It rejects malformed or non-positive components. This is still a *candidate* mapping; it performs no Events Manager lookup or mutation.

The isolated fixture test now includes composite timeslot IDs. No plugin is loaded, no data is migrated, and no checkout is replaced.

## Outstanding

Verify the actual installed plugin version and API, recurrence/occurrence mapping, capacity rules, cancellation exceptions, payment licensing, and whether a no-cost custom adapter can retain the existing checkout without duplicate booking records.
