# BH4 schema call-site audit

## Verified callers

In `includes/class-bh-manager.php`:

- `admin_post_bh3_install_missing_booking_tables` → `BH_Manager_3::install_missing_booking_tables()` → `BH_Schema_3::install_missing_booking_tables()`.
- `admin_post_bh3_install_schema` → `BH_Manager_3::install_schema()` → `BH_Schema_3::install()`.

Both handlers already required administrator permissions, WordPress nonce and staging hostname, with a typed confirmation; the booking-only action additionally required a confirmed backup. Both are now guarded by `BH4_ALLOW_STAGING_SCHEMA_CHANGES === true` at the action entrypoint **and** at the underlying schema method.

## Known limitation

This is a source-level call-site review of the manager and schema files, **not** proof that all possible dynamic invocations, plugins, or activation paths have been exhaustively searched. No WordPress site was exercised.

## Integration next steps

Keep existing `[bh_parent_booking]` checkout and Fluent Form 8 untouched until Events Manager has been tested against the multi-child, single-payment, fee, consent, confirmation, cancellation and payout contract. A read-only adapter should first inspect Events Manager availability without registering events, booking records, payment handlers or routes.

## No deployment

All modifications are in `feature/bh4-compatibility-foundation` of `bubbashub1/bubba4`. No running site, database, or original repo was changed.
