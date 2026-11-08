# BH4 database safety finding — family profile gender column

## Confirmed source behaviour
- `BH_Schema_3::install()` is documented as not automatic, but other schema-changing paths require separate review.
- `BH_Family_Native_Profiles_3::register()` registers `maybe_add_gender_column()` on `admin_init`.
- Prior to this branch, an administrator opening staging WP Admin could automatically run an `ALTER TABLE` on `wp_bh_family_profiles` if the gender column was absent.
- In this branch only, the operation additionally requires `BH4_ALLOW_STAGING_SCHEMA_CHANGES === true`; by default it does nothing.
- This guard is **not** a complete schema migration system; other potential writes still require review.

## Migration rule
Never set `BH4_ALLOW_STAGING_SCHEMA_CHANGES` in a real installation until an explicit, backed-up, reviewed schema migration is approved. Existing databases must be compared before any writes. This opt-in should ultimately be replaced by a versioned migration with dry-run and rollback instructions.

## Still to audit
- `class-bh-activity-parity.php`
- `class-bh-ask-support-storage.php`
- `class-bh-developments-admin.php`
- `class-bh-family-db-setup.php`
- `class-bh-field-manager.php`
- `class-bh-schema.php`
- `support-fluentforms-inbox.php`

The original `bubbawp` repository and all running WordPress installations remain unchanged.
