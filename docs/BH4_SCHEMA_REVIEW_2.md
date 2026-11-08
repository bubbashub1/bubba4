# BH4 database review — second pass

The read-only audit reports eight files containing possible schema-related operations. Manual source inspection distinguishes the following:

| Source | Observed path | Initial risk |
| --- | --- | --- |
| `includes/class-bh-schema.php` | Defines multiple `CREATE TABLE` statements and explicit install methods | High if manually invoked; do not invoke until table mapping reviewed |
| `includes/class-bh-family-db-setup.php` | Explicit admin-post create action uses `dbDelta`; can also `ALTER TABLE` | High; review confirmation and backups |
| `modules/Families/class-bh-family-native-profiles.php` | `admin_init` could run `ALTER TABLE` on staging for admin | Guarded in BH4 branch by `BH4_ALLOW_STAGING_SCHEMA_CHANGES` |
| `includes/class-bh-activity-parity.php` | Explicit staging admin-post migration, nonce and typed confirmation; `ALTER TABLE` adds columns | Guarded in BH4 branch by `BH4_ALLOW_STAGING_SCHEMA_CHANGES` |
| `includes/class-bh-ask-support-storage.php` | Admin-post install path uses `dbDelta` | Needs manual review before use |
| `includes/class-bh-field-manager.php` | Admin-post install uses `dbDelta`; other handlers write field data | Needs manual review before use |
| `modules/Support/support-fluentforms-inbox.php` | Admin-post support install uses `dbDelta`; also has transaction writes | Needs manual review before use |
| `includes/class-bh-developments-admin.php` | Contains SQL schema parsing in audit logic | Pattern match alone is not evidence of a schema write |

## Constraints

- These findings are from source inspection, not database introspection.
- Existing tables and booking data must remain unchanged until a separately approved migration.
- Do not enable the BH4 schema flag during ordinary testing.
- The added guards are on the Bubba4 development branch only, not in the original plugin.
- Other schema installation routes are not yet uniformly guarded. This is **not** clearance to deploy.

## Next review
1. Inspect each admin-post handler's capability and nonce protection.
2. Map every proposed table to the existing WordPress schema, without guessing whether a table exists.
3. Confirm whether any other hooks can write on page load.
4. Build a reversible migration manifest and run only on a disposable copy of staging.
