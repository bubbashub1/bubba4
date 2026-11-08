# BH4 staging database comparison — operator runbook

**Status:** prepared, not executed. Never run against production. The SQL file is `docs/data-foundation/BH4_STAGING_SCHEMA_COMPARISON_READ_ONLY.sql`.

1. Confirm the phpMyAdmin selected database is the **staging** database by checking `SELECT DATABASE()` against the hosting panel. Do not infer from a WordPress table prefix alone.
2. Confirm a recent staging backup and tested restore plan exist. No schema migration is permitted by this step.
3. Execute the read-only SQL and save only table, column and index metadata. Do not share or export records, customer emails, children, payment identifiers or form submissions.
4. Compare table naming, column types/nullability/defaults, indexes and collations with `plugin/bubba-hub-manager/includes/class-bh-schema.php` and the family schema modules.
5. Record differences in a review table with: source table, expected column/index, observed column/index, impact, proposed action, reviewer and date.
6. Mark the `BH4_Staging_Schema_Evidence_Gate` items as verified only when actual evidence exists; do not fabricate or pre-tick checks.
7. If any table is missing or different, **stop**. Design and test a versioned migration separately on a disposable staging copy. Do not run `dbDelta` or legacy installers automatically.

## Explicitly in scope
Activity, venue, organiser, session, activity-venue links, booking and family tables (children, bumps, favourites, planner, preferences). The queries match common prefix variations but must be inspected for omissions in the actual database.

## Not established
Current staging database state, row counts, actual schema parity, restore success, or any runtime compatibility. This is a metadata collection tool only.
