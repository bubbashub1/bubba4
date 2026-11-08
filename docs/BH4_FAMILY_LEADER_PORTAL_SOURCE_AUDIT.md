# BH4 Family and Leader portal source audit — activation blockers

Audited `plugin/bubba-hub-manager/includes/class-bh-manager.php` and repository file inventory on the development branch. **Source-level only; no WordPress runtime test.**

## Confirmed critical finding
The manager defines `FEATURE_DIRECTORY`, `FEATURE_LEADER`, `FEATURE_FAMILY`, and `FEATURE_BOOKINGS` as `false`. Many legacy modules are loaded and registered, but the flags are explicitly described as disabled until isolated staging QA. Do not change these to `true` as a shortcut to readiness.

## Family Hub: copied modules observed
Identity, read model, profile editor, native profiles, native favourites, actions, preferences, recommendations, account, notifications, privacy, schema audit, settings and hub. These are **source-presence findings**, not confirmation of successful rendering, permissions or database compatibility.

Required parity: login/ownership; child and bump create/edit; gender/year of birth; planner, favourites, visited; location/day/interests preferences; school/pregnancy reminders; account privacy and deletion/exports; mobile display.

## Leader Portal: copied modules observed
Leader account, dashboard, frontend editor, sessions, venues, business/storefront, bookings, statistics, support and UI/layout. Verify these are registered and accessible only to the intended leader account and scoped to owned organisers.

Required parity: class and venue ownership, timetable editing, booking enquiry/consent, confirmed enrolment, reminders, leader payment settings, messages, organisation editing and role-based access.

## High-priority integration blockers
1. Four feature flags are false; staging QA and explicit gating required before changing runtime behaviour.
2. Legacy modules may register hooks despite feature flags being false; perform an isolated WordPress bootstrap/hook inventory rather than assuming disabled flags suppress every side effect.
3. Actual staging SQL schema, membership/FluentAuth mapping and shortcode-to-page binding are not verified.
4. Forms and booking data must remain on the legacy path until Ninja Forms and Events Manager demonstrate complete parity.
5. User/child privacy, leader ownership and admin-only access need runtime permission tests.

## Staging exit criteria
Record a page-by-page family/leader smoke test, roles and expected shortcodes, SQL schema comparison, data ownership tests, error log review and rollback evidence. Keep old plugin installed identity and avoid side-by-side class loading.

**No code flags changed, plugin activated, schema written, form migrated, or payment flow switched.**
