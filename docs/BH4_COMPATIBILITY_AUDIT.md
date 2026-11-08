# BH4 compatibility audit — initial pass (2026-11-08)

## Verified source
- 170/170 original source file blob hashes matched after migration from bubbashub1/bubbawp main baseline bb0df614e3f0eb7dfb8d8695ec932b0c7ff9f817.
- Original source has not been modified by this migration.

## Confirmed from source entrypoints
- Root `bubbawp.php` still advertises **Bubba Hub Manager 3.0**, text domain `bubbawp`, GitHub Plugin URI `bubbashub1/bubbawp` and requires the nested manager bootstrap.
- Nested `plugin/bubba-hub-manager/bubba-hub-manager.php` defines `BUBBA_HUB_MANAGER_VERSION` and `BUBBA_HUB_MANAGER_PATH`, then boots `BH_Manager_3` on `plugins_loaded`.
- `BH_Manager_3` registers existing family, leader, directory, booking, marketplace and statistics modules; existing `BH3_ENABLE_SUPPORT` staging flag is present.
- Class/constant collision risk means this repository is **not safe for simultaneous activation** with BubbaWP 3.0. Renaming just the plugin display name would not solve the issue.
- A source file copy is **not** proof that the runtime functions or third-party plugin integrations work.

## Decisions held
- Keep Simple Membership for Family and Leader Free/Pro membership; preserve FluentAuth and existing membership mapping until verified.
- No Ultimate Member or LatePoint.
- Events Manager, NiroHelp and Ninja Forms are proposed adapters, not yet tested or enabled.
- Retain existing Fluent Forms booking and support paths until replacement is proven end-to-end.
- Preserve existing custom SQL tables and current production/staging configuration.

## Next safe implementation gates
1. Inventory every plugin entrypoint, class, constant, activation hook, database migration, shortcode and option namespace.
2. Audit Simple Membership role/access and paid renewals without altering existing accounts.
3. Evaluate Events Manager class/session mapping and fees using isolated fixtures, without installing it into the current site yet.
4. Plan adapters with feature flags; do not replace live booking checkout prematurely.
5. Run PHP syntax, unit/smoke checks and side-by-side behavior comparison on an isolated environment before deployment.

**No activation, database migration, plugin installation or staging deployment has been performed.**
