# BH4 hook inventory findings — source scan 2026-11-08

Source: GitHub Actions run 42471725291, commit `261a501e`, read-only scan of plugin PHP source. **CI passed**.

| Static match category | Count |
| --- | ---: |
| Activation hooks | 0 |
| Deactivation hooks | 0 |
| Uninstall hooks | 0 |
| WordPress scheduled events | 0 |
| WordPress scheduled-event clearing | 0 |
| Action Scheduler calls | 0 |
| Cron-named hook registrations | 0 |
| Booking/payment hook registrations | 5 |
| Booking-related shortcode registrations | 4 |

Broader static inventory: 103 PHP source files, 93 classes, 37 shortcode names, 0 duplicate class definitions **within this repository**, and 8 potential schema-changing files.

## Interpretation
- The scan found no direct scheduling/activation API calls in the scanned plugin files; dynamic registration, plugins outside this repository, and callback-side effects remain unverified.
- Booking/payment hooks and shortcodes require manual ownership, nonce, permission, data handling and idempotency review before changing checkout integration.
- No duplicate class names **within Bubba4** does not mean Bubba4 can coexist with BubbaWP: both copies still declare the same legacy classes.
- The schema-changing source files remain subject to staging-only guards and separate database review.
- Source scan is not a runtime smoke test, staging schema verification, or approval to activate.

## Next review
Inspect each of the five booking/payment hook registration sites and four booking shortcode registration sites, including callbacks and dependencies. Review `admin_post` and AJAX hooks separately because the keyword scan does not establish their safety. Confirm scheduled work in other installed plugins via staging inspection only after approval.

**Decision:** keep the controlled same-plugin replacement blocked; no merge, activation, data writes, or checkout switch.
