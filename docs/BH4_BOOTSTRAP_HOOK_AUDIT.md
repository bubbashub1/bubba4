# BH4 bootstrap, hooks and shortcode migration audit — pass 1

**Scope:** source review of `plugin/bubba-hub-manager/bubba-hub-manager.php` and `includes/class-bh-manager.php`; not a complete cross-file hook inventory. No WordPress instance inspected.

## Confirmed bootstrap facts
- Plugin header remains `Bubba Hub Manager 3.0`, version `0.1.0`; constants `BUBBA_HUB_MANAGER_VERSION` and `BUBBA_HUB_MANAGER_PATH` are defined without collision guards.
- Main file registers `BH_Manager_3::boot` on `plugins_loaded`.
- Manager guards repeated `boot()` calls with `self::$booted`, but **this does not prevent two separate plugin copies from declaring the same class**.
- Directory, leader, family and booking feature constants are `false`; nevertheless many module `register()` calls and admin handlers are wired during boot. Do not assume all features are inert solely from these constants.
- Support modules load only when `BH3_ENABLE_SUPPORT === true` and host is `staging.bubbahub.co.uk`.
- Manager registers `[bh_admin_activity_wizard]` and invokes directory/preview, activity-detail and family-hub registration. Other shortcodes are registered inside their module classes and require separate review.
- Manager registers schema installer handlers `admin_post_bh3_install_schema` and `admin_post_bh3_install_missing_booking_tables`; existing staging guards must remain enforced.
- `admin_post_bh3_update_activity` is registered both directly during boot and again inside an `admin_init` callback. This may invoke the same save handler twice if both hooks are reached; requires targeted staging-independent fix and regression test before upgrade.
- Manager registers many state-changing `admin_post_bh3_*` actions for venue, organiser, session, leader, family and settings operations. These must retain nonce, permission and ownership checks.

## Upgrade compatibility gates
1. Preserve shortcode names, admin-post action names, and existing saved page content in the first same-plugin upgrade.
2. Audit every module `register()` method for shortcode, REST, AJAX, cron and scheduled-action registration.
3. Remove or prove harmless duplicate action registrations, especially activity update.
4. Inventory activation/deactivation hooks and scheduled events across all PHP source before packaging.
5. Verify all write handlers enforce capability, nonce, owner checks, and staging/production policy where appropriate.
6. Review FluentAuth, Simple Membership, FluentSMTP and checkout hook ownership without switching integrations.
7. Validate staging plugin identity and rollback separately; never activate two copies.

**Status:** BLOCKED. Source review is incomplete and staging parity not established. This document does not authorise deployment.
