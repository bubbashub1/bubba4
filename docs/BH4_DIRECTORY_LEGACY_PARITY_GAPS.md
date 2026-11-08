# BH4 directory: legacy parity review (source inspection)

Reviewed `class-bh-directory.php` against the isolated BH4 filter, query, SELECT and response contracts. This is **source-level analysis**, not staging verification.

## Already represented in BH4 planning
Published-only activity filtering; title/description/organiser keyword search; category; town/region/postcode; age including newborn age 0; weekday; term-time; free-only and maximum price; title/town/newest sorting; page offsets; 5/10/20/50-mile bounding box and great-circle distance. Query execution remains disabled.

## Differences and release blockers
1. **Card fields:** legacy SELECT includes `description`, `image_path`, `age_min_months`, `age_max_months`, `price_from`, `is_free`, `organiser`, and `town`. BH4 public contract intentionally exposes only id, title, slug, category, town and price label. Design approval required for images, age labels, organiser names and summaries; do not automatically expose raw descriptions.
2. **Sessions:** legacy directory separately queries weekly scheduled sessions for displayed timetables; BH4 SELECT currently has no timetable aggregation. Session availability must not be invented.
3. **Map:** legacy directory builds map points after fetching activities; BH4 public response has no map projection. Location data and precision need review.
4. **Input:** legacy uses `bh_*` GET names and sanitisation/fallbacks. BH4 accepts typed filter keys and fails closed on malformed inputs. A dedicated web input adapter is needed; native apps should use typed filters.
5. **Pagination:** legacy fixed 12 per page, BH4 contract allows 1..24; defaults 12. Decide whether 24 is an API-only option.
6. **Sort stability:** BH4 adds ID tie-breakers; legacy title/town sorts can have unstable ties. This is a deliberate determinism improvement but can change order for duplicate titles.
7. **Geo:** both planners have bounding-box limitations near the antimeridian and poles. Reject unsupported regions before integration rather than silently omitting matching venues.
8. **Schema:** actual staging columns and types remain unverified; the passive checker does not inspect a database.
9. **Safety:** no SQL execution, API routes, WordPress bootstrap or deployment is authorised.

## Next acceptance sequence
Verify staging schema read-only; compare identical legacy and candidate search fixtures and returned IDs; verify card fields and sessions; inspect privacy and performance; agree upgrade/collision plan; then request explicit permission before any staging-only integration.
