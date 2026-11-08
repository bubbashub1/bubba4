# BH4 recurring-session compatibility research checklist

## Distinguish the three models

1. **Bubba Hub activity:** the public listing, its leader and venue relationships.
2. **Bubba Hub session:** an existing timetable entry, which may describe a repeating class.
3. **Events Manager event/recurrence:** an external plugin's event record or generated occurrence. Never assume the identifiers or recurrence semantics are interchangeable.

## Verify against the actual installed Events Manager version

- Whether recurring event templates and generated occurrences have separate IDs.
- Whether booking availability and capacity belong to the recurrence or each occurrence.
- How term-time gaps, bank holidays, one-off cancellations and changed venues are represented.
- How timezone and daylight-saving changes are handled for local Devon/Cornwall times.
- Whether booking records point to a recurrence parent or a dated occurrence.
- Whether editing a recurring series modifies already-booked occurrences.
- Whether the free edition supports the required booking and payment integration.

## Safe mapping rules

A BH session may require many dated Events Manager occurrences. Do not automatically create or sync anything until a real schema/API inspection establishes cardinality and cancellation semantics. Keep source IDs, occurrence date and local timezone distinct. Never infer a booking from a timetable entry.

## Validation status

**Not tested against Events Manager.** This checklist is an investigation plan, not confirmation of compatibility. Keep `[bh_parent_booking]` and existing Stripe checkout in place.
