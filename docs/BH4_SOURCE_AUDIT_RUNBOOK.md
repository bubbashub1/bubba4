# BH4 source audit runner

The script `scripts/bh4_static_audit.py` produces a JSON inventory of PHP class names, shortcode registrations, constants, activation hooks, potential schema-changing statements and include statements.

Run from a checkout:

```sh
python3 scripts/bh4_static_audit.py > bh4-static-audit.json
python3 scripts/bh4_static_audit.py --strict > /dev/null
find plugin -name '*.php' -print0 | xargs -0 -r -n1 php -l
php -l bubbawp.php
```

The script is **read-only**. It uses approximate source patterns and is not a substitute for a WordPress runtime test. A repeated shortcode registration can be intentional; a single class definition can still conflict with the separately installed BubbaWP plugin. Its output should not contain customer data.

## Migration acceptance criteria

- [x] All 170 source files copied with matching Git blob SHA values at initial migration baseline.
- [x] Original BubbaWP repository left untouched.
- [x] Simple Membership retained; Ultimate Member and LatePoint excluded.
- [x] Draft PR isolates new diagnostic work from the main branch.
- [ ] Run static audit and review duplicate classes, hooks and potential schema changes.
- [ ] Resolve simultaneous activation collisions or explicitly plan a replacement-only upgrade.
- [ ] Test compatibility of Simple Membership, authentication and existing shortcodes.
- [ ] Confirm Events Manager can satisfy recurring class booking, child selection, payment fee and ticketing requirements before any checkout replacement.
- [ ] Validate Ninja Forms migration and NiroHelp permissions on isolated staging.
- [ ] Complete booking, consent, refund, email, payout, permissions and rollback tests.
- [ ] Only then approve deployment.

**Do not deploy this repository yet.**
