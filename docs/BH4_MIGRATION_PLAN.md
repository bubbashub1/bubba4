# Bubba Hub 4.0 integration plan

## Migration status
- Source snapshot: bubbashub1/bubbawp main at bb0df614e3f0eb7dfb8d8695ec932b0c7ff9f817.
- Verified 170 of 170 original source blob hashes match the files copied to bubbashub1/bubba4.
- This is a source snapshot, not a deployment, activated plugin, database migration or functional compatibility test.
- Source Git commit history was not transferred; source commit SHA is retained for provenance.

## Minimal-plugin architecture
1. BubbaWP native listings remain authoritative for custom activity, venue, organiser, schedule and family data. Preserve existing SQL tables and contracts.
2. Events Manager is a **candidate** for recurring sessions, capacity, booking and tickets; verify free-version capabilities, checkout/Stripe fees, child bookings, refunds and payouts before switching.
3. NiroHelp is a **candidate** for knowledge base, private support and ticketing; verify free features, user permissions, moderation and leader contributions.
4. Ninja Forms is the preferred replacement for forms and enquiries. Keep existing Fluent Forms and shortcodes working until replacement forms and submission handling are tested.
5. Leader Portal stays in BubbaWP; integrate listings, schedules, bookings, enquiries, payments and statistics. Reuse native data; avoid another portal or statistics plugin.
6. Preserve existing authentication, memberships, SMTP, Action Scheduler and BubbaTheme where compatible.

## Safety gates
- Do not activate the migrated plugin alongside BubbaWP before checking duplicate PHP class/function names, hooks, options, shortcodes and database migrations.
- Do not deploy the copied workflows unchanged without reviewing their triggers and secrets.
- Keep old booking checkout operational until all end-to-end tests pass.
- Never copy or alter live customer/payment data as part of this source migration.
- Implement adapters behind feature flags and test each on staging with rollback procedures.

## Next work
- Audit source entrypoints and dependencies, identify side-effecting activation code and workflow triggers.
- Add passive compatibility diagnostics for optional plugins, without installing or invoking them.
- Validate PHP syntax and smoke tests before any staging deployment.
