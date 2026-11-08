# BH4 schema installation safeguards — handler review

Reviewed six schema-changing paths and added a disabled-by-default guard on the Bubba4 feature branch:

| Handler | Existing protection | BH4 addition |
| --- | --- | --- |
| Family native profiles `maybe_add_gender_column` | Staging hostname, administrator | Explicit `BH4_ALLOW_STAGING_SCHEMA_CHANGES` |
| Activity parity `migrate` | Staging, administrator, nonce, typed confirmation | Explicit flag |
| Family DB setup `create` | Staging, administrator, nonce, backup confirmation | Explicit flag |
| Ask Support storage `install` | Staging, administrator, nonce, typed confirmation | Explicit flag |
| Field manager `install` | Guard includes staging/admin/nonce; typed confirmation | Explicit flag |
| Support Fluent Forms inbox installer | Staging, administrator, nonce, typed confirmation | Explicit flag |

The flag defaults to disabled if undefined. **Do not define it as true** until a backed-up isolated database migration has been approved.

These changes do not disable ordinary Family Hub profile saving or other existing non-schema actions. They do not automatically gate every possible schema operation: `BH_Schema_3::install()`, booking-table installer calls, and other dynamic code paths still require explicit caller analysis.

## Pending
- Inspect all call sites of `BH_Schema_3::install()` and `install_missing_booking_tables()`.
- Audit any schema-changing methods outside these six handlers.
- Verify updated PHP syntax in GitHub Actions.
- Compare actual staging schema to the source manifest in a read-only process before any migration.

No website or database was modified.
