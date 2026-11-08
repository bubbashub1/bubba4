# BH4 core and booking schema safety

Source review found two additional schema installers in `BH_Schema_3`:

- `install_missing_booking_tables()`: previously required staging administrator, and attempted to create only missing booking tables.
- `install()`: previously required administrator permission but did **not** require the staging hostname; it can call `dbDelta()` for all declared tables and update the schema version option.

Both now additionally require `BH4_ALLOW_STAGING_SCHEMA_CHANGES === true`. The full installer also requires the exact staging hostname. This change is **only in the Bubba4 development branch**.

## Why this matters

The full installer could modify an existing schema through `dbDelta`; it must not be invoked in production as part of a migration. The booking-only installer is less invasive but still creates tables and needs a verified schema and backup.

## Migration rules

1. Do not enable the flag on a running site before the full migration review.
2. Inventory all callers and activation hooks; a guarded method is not proof that every other write path is safe.
3. Compare schema definitions against an actual database snapshot read-only.
4. Prefer a dedicated versioned migration plan over the legacy general installer.
5. Test on a disposable staging copy, including rollback, before authorising any schema changes.

The original BubbaWP plugin and databases have not been modified.
