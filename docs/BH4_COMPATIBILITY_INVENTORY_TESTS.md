# BH4 compatibility foundation — test contract

This branch adds a passive administrator-only compatibility inventory. It is **not** loaded by the current plugin bootstrap and therefore cannot change live behaviour.

## What it reads
- Installed plugin metadata via the WordPress `get_plugins()` API.
- Active plugin basenames, including network-activated plugins.
- Existence of relevant shortcodes and the PHP/WordPress versions.

## What it does not do
- Does not install, activate, update or deactivate plugins.
- Does not read users, bookings, family records, payments, form submissions or customer information.
- Does not write to the database, register routes or send external requests.
- Does not assume that installed or active means compatible.
- Does not automatically replace Simple Membership or existing Fluent Forms booking paths.

## Verification before integration
1. PHP syntax-check the new class.
2. Confirm no `require` or `include` from the current bootstrap points to it.
3. In a separate test installation, explicitly load the file and call `BH4_Compatibility_Inventory::inspect()` as an administrator; verify a non-admin receives `WP_Error`.
4. Compare detected basenames to actual installed plugins; add any alternative official basenames after confirming them.
5. Only after an explicit decision, wire the inventory to a read-only admin screen.

## Membership decision
Keep **Simple Membership**. Do not add Ultimate Member. Paid membership renewals, access gates and checkout buttons still require isolated staging QA.

## Deployment gate
Do not deploy or activate Bubba4 until plugin namespace collision remediation, source compatibility checks, booking/Stripe tests and rollback preparation are complete.
