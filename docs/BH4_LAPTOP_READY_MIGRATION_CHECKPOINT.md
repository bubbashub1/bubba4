# BH4 laptop-ready migration checkpoint (source-backed)

This is a **pre-staging** checkpoint, not a release sign-off. Latest completed GitHub Actions run before this checkpoint: `b944282e` succeeded. Isolated PHP tests and static checks do not demonstrate full WordPress runtime parity.

| Area | Verified in source / automated checks | Still required before staging sign-off |
| --- | --- | --- |
| Plugin migration | 170/170 legacy blobs copied and checked; PHP and bootstrap checks in CI | Isolated activation, upgrade gate and class collision tests against installed site |
| Custom directory | Pure read-only filter, query, card, map and timetable fixtures | Compare actual SQL schema, sample data and responsive AJAX rendering |
| Family Hub | Legacy family modules retained in copied source | Login, children/bumps, preferences, planner, saved/visited and permissions |
| Leader Portal | Legacy modules retained in copied source | Organisation/venue CRUD, schedules, bookings, enquiries, payments and role permissions |
| Auth/memberships | FluentAuth and Simple Membership preserved in architecture | Free/Pro role mapping, renewals, redirects, guest flows |
| Checkout | Existing Form 8 and `bh_parent_booking` retained; safety contracts and static guards | Stripe test-mode end-to-end payment, webhook, booking holds, duplicate retries and refunds |
| Payment recovery | Pure decision/reconciliation/report/access contracts with CI fixtures | Actual Stripe lookup, DB-safe reconciliation, admin screen and audit logging |
| Events Manager | Version-aware session mapping fixtures | Free edition licensing/capabilities, recurrence, timeslots, capacity and booking parity |
| Ninja Forms | Preferred future forms adapter, legacy Fluent Forms preserved | Actual form definitions, consent, submissions, notification routing and migration validation |
| Theme | Existing BubbaTheme kept separate | Full-width layout, header/footer, directory, responsive family/leader screens |

## Four-hour laptop session: suggested order
1. **Backups and isolation (20 min):** verify staging DB/file backup and rollback; confirm current plugin slug, active plugin list, PHP/WP versions. Do not activate two copies of the same classes.
2. **Database read-only comparison (35 min):** inspect actual custom table names/columns and compare against source expectations. Do not run automatic migrations.
3. **Family and leader smoke test (60 min):** role-scoped navigation, profile CRUD, permissions, venue/activity/schedule editing; record broken URLs.
4. **Directory and theme smoke test (40 min):** search, AJAX, filters, map/timetable, activity permalink, mobile.
5. **Checkout sandbox test (65 min):** one unpaid hold, one successful payment, duplicate click, delayed webhook, cancellation and guest flow. Use test-only data, never real cards.
6. **Triage and rollback decision (20 min):** collect red/amber/green results and choose next code fixes.

## Hard blockers to a full migration
- **Same plugin identity and classes:** BH4 cannot safely be activated alongside the old plugin; requires controlled replacement with rollback.
- **No confirmed staging database schema parity.**
- **No real end-to-end WordPress checkout/Stripe verification.**
- **Events Manager and Ninja Forms adapters are not fully implemented or proven equivalent.**
- **Read-only reconciliation helpers are not a functioning admin screen or Stripe recovery service.**

## Go/no-go criteria
A staging candidate is acceptable only after source lint/tests pass, all expected tables are read-only verified, activation plan avoids class collisions, and rollback is rehearsed. Production deployment requires complete family/leader/directory parity, consent and booking payment tests, security review and explicit approval.

**No merge, deploy, schema write, production change or plugin switch is authorised by this document.**
