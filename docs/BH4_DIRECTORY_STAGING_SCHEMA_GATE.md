# BH4 directory staging schema verification gate

Status: **NOT VERIFIED**. No live/staging database inspection has been performed by this document. Do not activate the new directory query or expose it through REST until this gate is signed off.

## Read-only staging checks

Run the following in the staging database with a read-only account, substituting the actual WordPress table prefix. Do **not** run ALTER, CREATE, UPDATE, DELETE, or install/migration handlers.

```sql
SHOW COLUMNS FROM wp_activities;
SHOW COLUMNS FROM wp_organisers;
SHOW COLUMNS FROM wp_venues;
SHOW COLUMNS FROM wp_activity_venues;
SHOW COLUMNS FROM wp_sessions;
```

Verify these exact columns and types before enabling any query:
- activities: id, organiser_id, title, slug, description, status, category, is_free, price_from, age_min_months, age_max_months
- organisers: id, name
- venues: id, name, town, region, postcode, latitude, longitude
- activity_venues: activity_id, venue_id
- sessions: activity_id, day_of_week, term_time_only

Also verify that the published status values are `published` / `publish`, day-of-week uses Monday=1 through Sunday=7, prices use GBP units rather than pence, and `is_free` uses 1 for free.

## Query parity and privacy

With non-personal staging fixtures, compare the existing directory and the BH4 proposed query for keyword/organiser search, newborn age, free-only, price ceiling, town/region/postcode, weekday, term time, sorting, pagination, and 5/10/20/50-mile proximity. Confirm the first venue selected for town sorting matches the current directory. Check missing venue coordinates, NULL prices, empty search, multiple venues, and page boundaries.

The proposed SQL must only be executed via `$wpdb->prepare( $sql, $params )` after the schema gate. The current BH4 planner and SELECT builder are **non-executing** and explicitly set `execution_allowed=false`. Do not bypass that guard. The response adapter assumes trusted published-only SELECT rows; never feed arbitrary database rows into it.

## Release blockers

- Staging DDL not yet checked; draft field map is not evidence of real columns.
- No end-to-end database-backed parity results.
- No registered API route or production authentication/rate-limit review.
- No native app integration.
- Bubba4 and BubbaWP PHP class/entrypoint collisions still require a safe upgrade plan.

Approval requires recorded staging query evidence, public-field review, regression results, and explicit user permission before deployment.
