# BH4 directory staging verification runbook

**Status:** Not performed. Planning only. No staging credentials, database connection, plugin activation, migrations or deployment.

## Preconditions
- Keep Bubba4 PR #1 as a draft. Never activate Bubba4 alongside BubbaWP: PHP classes and entrypoints currently collide.
- Confirm a current staging backup, correct staging host and table prefix. No live database access.
- Obtain explicit permission before running even read-only staging commands; these fixtures are not permission to connect.
- Limit access to authorised administrators. Never export child/family records, contact details or precise user locations.

## Read-only schema inventory
Run the following only in a confirmed staging read-only SQL session, with `{prefix}` replaced by the verified actual WordPress prefix:

```sql
SHOW COLUMNS FROM `{prefix}activities`;
SHOW COLUMNS FROM `{prefix}organisers`;
SHOW COLUMNS FROM `{prefix}venues`;
SHOW COLUMNS FROM `{prefix}activity_venues`;
SHOW COLUMNS FROM `{prefix}sessions`;
```

Compare against `BH4_Directory_Schema_Check` and the SELECT planner. Confirm the actual coordinate names `latitude` and `longitude` (not the draft `lat`/`long`), status values, session frequency/day/time types, currency units, organiser and venue joins, and index coverage.

## Fixture parity, without sensitive exports
1. Confirm the existing shortcode `[bubbahub_directory]` still works on staging, without enabling Bubba4.
2. For each `BH4_Directory_Scenarios::all()` case, capture only activity IDs, order and public card fields from the legacy query. Never capture family profiles or booking data.
3. Prepare the equivalent BH4 query using the non-executing planner; review SQL and placeholders before any execution is approved.
4. Compare ordered IDs via `BH4_Directory_Parity`; compare public card fields via `BH4_Directory_Field_Parity`.
5. Check age 0, free-only price override, Monday term time, organiser search, town, postcode, region, newest/town sort, pagination and Near Me with synthetic coordinates.
6. Compare published-only activity-venue map markers and weekly scheduled session previews. Confirm multiple venues, duplicate links, cancelled sessions, empty times and missing coordinates.
7. Compare legacy card images, organiser names, age ranges and price labels; approve any deliberate differences.
8. Inspect performance of read-only queries with staging-only EXPLAIN (no EXPLAIN ANALYZE without review), and review large result sets.
9. Confirm privacy: no family or leader contact details, no user coordinates retained, no public booking availability claims.
10. Record reviewer, staging schema date, fixture results, deviations, and rollback approach. Leave `staging_verified=false` until actual verification.

## Stop conditions
Missing columns; unknown price units; ambiguous session/venue joins; inconsistent published status; incorrect age 0 handling; unexpected private fields; unsafe SQL; failed CI; or any evidence that a plugin activation would collide with BubbaWP. Do not merge, deploy, switch checkout ownership or run schema installers.

## Separate outstanding work
Bubba4 upgrade/namespace collision plan, Events Manager booking parity and Stripe cost/consent/refund checks, REST auth and rate limits, actual staging DDL verification. CI success alone does not clear these gates.
