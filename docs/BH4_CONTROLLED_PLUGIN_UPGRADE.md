# BubbaWP 3.0 → Bubba4 controlled upgrade plan

**Decision: not approved for activation.** This is a planning document only. Bubba4's copied plugin entrypoint is still `plugin/bubba-hub-manager/bubba-hub-manager.php`, with plugin header `Bubba Hub Manager 3.0`, version `0.1.0`, constants `BUBBA_HUB_MANAGER_VERSION`/`BUBBA_HUB_MANAGER_PATH`, and `BH_Manager_3::boot` on `plugins_loaded`. These are shared with BubbaWP; the two must **never** be active together.

## Recommended strategy: same-plugin upgrade, not side-by-side activation
1. Keep Bubba4 as a source repository and draft PR until all parity gates pass. Preserve the **existing installed plugin directory and main PHP file path** as the upgrade identity, rather than introducing a second plugin.
2. Record installed plugin header, directory, WordPress active_plugins option, current table prefix and current DB schema on staging (read-only). Do not guess the installed plugin's exact path from this repository.
3. After complete review, produce a staging-only upgrade package targeting that **same plugin identity**, with backups and a rollback package. Any header/version change must be coordinated with the actual deployment mechanism and WordPress upgrade semantics.
4. Keep existing PHP classes, public shortcodes, database tables, saved options, URLs and booking ownership stable during the first like-for-like upgrade. Refactor behind interfaces only after parity is proven.
5. Confirm no second copy of `BH_Manager_3` or duplicate `BUBBA_HUB_MANAGER_*` definitions can load. A plugin filename change alone does not solve class collisions.
6. Review all hooks, activation/deactivation callbacks, cron jobs, scheduled actions and installers. Schema mutations remain guarded; no automatic data migration.
7. Staging sequence after **explicit approval**: backup → inspect plugin identity → disable risky upgrade side effects → replace plugin files at same identity → smoke-test admin, directory, activity pages, family/leader portals, bookings and auth → rollback immediately on failure.
8. Only consider live rollout after staging sign-off and a separate explicit deployment decision.

## Mandatory gates
- CI lint/static tests green.
- Staging schema verified read-only, directory parity and card/map/timetable fixtures reviewed.
- Membership Simple Membership and FluentAuth flows preserved.
- Existing booking checkout/Stripe ownership and terms remain unchanged; Events Manager migration is separately gated.
- No family or child data exported or overwritten.
- A documented rollback path exists, with exact deployed package hash and plugin identity.
- No activation of a second copy, no merge/deploy before user approval.

## Unresolved
Actual installed plugin path/slug, deployed code version, database schema, hook inventories, activation callbacks, WordPress deployment tool behaviour, and staging backup/rollback capability are not yet verified. Treat this plan as blocked until confirmed.
