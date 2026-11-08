# Events Manager recurrence model: preliminary source findings

## Source inspected

Historical WordPress plugin mirror: https://github.com/wp-plugins/events-manager/blob/master/classes/em-event.php

**Version warning:** the mirror's last push was in June 2019. These findings are *not* verified against the currently installed Events Manager version, and must not be used as a production API contract.

## Observed in historical source

- `EM_Event` documentation distinguishes a recurring event, an individual occurrence, and a standalone event.
- It warns that editing a recurring group can overwrite changes to individual events.
- It declares distinct `event_id` and `recurrence_id` properties.
- Date fields include `event_start_date` and `event_end_date`.
- Recurrence fields include `recurrence_interval`, `recurrence_freq`, `recurrence_byday` and `recurrence_byweekno`.
- RSVP/booking-related fields include `event_rsvp` and `event_rsvp_spaces`.

## Implications for Bubba Hub

Do not model one Bubba Hub session ID as always equal to one Events Manager event ID. A recurring series may generate distinct occurrences; the exact cardinality and ownership must be verified against the installed plugin. Editing a series could affect exceptions and already-booked dates.

Keep mapping candidates as `unverified`. No automatic recurrence sync, cancellation, booking import, payment or schema mutation.

## Next validation gate

Inspect the exact Events Manager version intended for staging and its current PHP API/schema, then build version-specific read-only fixtures for parent series, occurrence IDs, exclusions, capacity and booking references. Do not rely on this historical mirror for implementation.
